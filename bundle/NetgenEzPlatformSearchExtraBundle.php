<?php

namespace Netgen\Bundle\EzPlatformSearchExtraBundle;

use Netgen\EzPlatformSearchExtra\Container\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class NetgenEzPlatformSearchExtraBundle extends Bundle
{
    public function build(ContainerBuilder $containerBuilder)
    {
        parent::build($containerBuilder);

        // Needs to be added first because other passes depend on it
        $containerBuilder->addCompilerPass(new Compiler\TagSubdocumentCriterionVisitorsPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateContentSubdocumentMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateContentTranslationSubdocumentMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateSubdocumentQueryCriterionVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldType\RichTextIndexablePass());
        $containerBuilder->addCompilerPass(new Compiler\FieldType\XmlTextIndexablePass());
        $containerBuilder->addCompilerPass(new Compiler\SearchResultExtractorPass());
        $containerBuilder->addCompilerPass(new Compiler\RawFacetBuilderDomainVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldTypeRegistryPass());
    }
}
