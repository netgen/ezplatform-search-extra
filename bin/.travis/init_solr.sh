#!/usr/bin/env bash

default_config_files[1]='vendor/ezsystems/ezplatform-solr-search-engine/lib/Resources/config/solr/schema.xml'
default_config_files[2]='tests/lib/Resources/config/search/solr/custom-fields-types.xml'
default_config_files[3]='vendor/ezsystems/ezplatform-solr-search-engine/lib/Resources/config/solr/language-fieldtypes.xml'

default_cores[0]='core0'
default_cores[1]='core1'
default_cores[2]='core2'
default_cores[3]='core3'
default_cores[4]='core4'
default_cores[5]='core5'

SOLR_PORT=${SOLR_PORT:-8983}
SOLR_VERSION=${SOLR_VERSION:-8.11.2}
SOLR_DEBUG=${SOLR_DEBUG:-false}
SOLR_HOME=${SOLR_HOME:-ez}
SOLR_CONFIG=${SOLR_CONFIG:-${default_config_files[*]}}
SOLR_CORES=${SOLR_CORES:-${default_cores[*]}}
SOLR_DIR=${SOLR_DIR:-__solr}
SOLR_INSTALL_DIR="${SOLR_DIR}/${SOLR_VERSION}"

download() {
    case ${SOLR_VERSION} in
        7.7.2 )
            url="http://archive.apache.org/dist/lucene/solr/${SOLR_VERSION}/solr-${SOLR_VERSION}.tgz"
            ;;
        8.11.2 )
            url="https://dlcdn.apache.org/lucene/solr/${SOLR_VERSION}/solr-${SOLR_VERSION}.tgz"
            ;;
        *)
            echo "Version '${SOLR_VERSION}' is not supported or not valid"
            exit 1
            ;;
    esac



    create_dir ${SOLR_DIR}

    archive_file_name="${SOLR_VERSION}.tgz"
    installation_archive_file="${SOLR_DIR}/${archive_file_name}"

    if [ ! -d ${SOLR_INSTALL_DIR} ] ; then
        echo "Installation ${SOLR_VERSION} does not exists"

        if [ ! -f ${installation_archive_file} ] ; then
            echo "Installation archive ${archive_file_name} does not exist"
            echo "Downloading Solr from ${url}..."
            curl -o ${installation_archive_file} ${url}
            echo 'Downloaded'
        fi

        echo "Extracting from installation archive ${archive_file_name}..."
        create_dir ${SOLR_INSTALL_DIR}
        tar -zxf ${installation_archive_file} -C ${SOLR_INSTALL_DIR} --strip-components=1
        echo 'Extracted'
    else
        echo "Found existing ${SOLR_VERSION} installation"
    fi
}

copy_files() {
    destination_dir_name=$1
    shift
    files=("$@")

    for file in ${files} ; do
        copy_file ${file} ${destination_dir_name}
    done
}

copy_file() {
    file=$1
    destination_dir=$2

    if [ -f "${file}" ] ; then
        cp ${file} ${destination_dir}
        echo "Copied file '${file}' to directory '${destination_dir}'"
    else
        echo "${file} is not valid"
        exit 1
    fi
}

create_dir() {
    dir_name=$1

    if [ ! -d ${dir_name} ] ; then
        mkdir ${dir_name}
        echo "Created directory '${dir_name}'"
    fi
}

exit_on_error() {
    message=$1

    echo "ERROR: ${message}"
    exit 1
}

configure() {
    home_dir="${SOLR_INSTALL_DIR}/server/${SOLR_HOME}"
    template_dir="${home_dir}/template"
    config_dir="${SOLR_INSTALL_DIR}/server/solr/configsets/_default/conf"

    create_dir ${home_dir}
    create_dir ${template_dir}

    files=${SOLR_CONFIG}
    files+=("tests/lib/Resources/config/search/solr/${SOLR_VERSION}/solrconfig.xml")
    files+=("${config_dir}/stopwords.txt")
    files+=("${config_dir}/synonyms.txt")

    copy_files ${template_dir} "${files[*]}"
    copy_file "${SOLR_INSTALL_DIR}/server/solr/solr.xml" ${home_dir}

    # modify solrconfig.xml to remove section that doesn't agree with our schema
    # Adapt autoSoftCommit to have a recommended value, and remove add-unknown-fields-to-the-schema
    sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema".*/,/<\/updateRequestProcessorChain>/d' "${template_dir}/solrconfig.xml"
    sed -i.bak2 's/${solr.autoSoftCommit.maxTime:-1}/${solr.autoSoftCommit.maxTime:20}/' "${template_dir}/solrconfig.xml"
}

run() {
    echo "Running with version ${SOLR_VERSION} in standalone mode"
    echo "Starting solr on port ${SOLR_PORT}..."

    ./${SOLR_INSTALL_DIR}/bin/solr -p ${SOLR_PORT} -s ${SOLR_HOME} -Dsolr.disable.shardsWhitelist=true || exit_on_error "Can't start Solr"

    echo "Started"

    create_cores
}

create_cores() {
    home_dir="${SOLR_INSTALL_DIR}/server/${SOLR_HOME}"
    template_dir="${home_dir}/template"

    for solr_core in ${SOLR_CORES} ; do
        if [ ! -d "${home_dir}/${solr_core}" ] ; then
            create_core ${solr_core} ${template_dir}
        else
            echo "Core ${solr_core} already exists, skipping"
        fi
    done
}

create_core() {
    core_name=$1
    config_dir=$2

    ./${SOLR_INSTALL_DIR}/bin/solr create_core -c ${core_name} -d ${config_dir} || exit_on_error "Can't create core"
}

download
configure
run
