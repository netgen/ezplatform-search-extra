Installation instructions
=========================

To install eZ Platform Search Extra first add it as a dependency to your project:

.. code-block:: shell

    $ composer require netgen/ezplatform-search-extra:^1.0

Once Search Extra is installed, activate the bundle in ``app/AppKernel.php`` file by adding it to
the ``$bundles`` array in ``registerBundles()`` method, together with other required bundles:

.. code-block:: php

    public function registerBundles()
    {
        ...

        $bundles[] = new Netgen\Bundle\EzPlatformSearchExtraBundle\NetgenEzPlatformSearchExtraBundle;

        return $bundles;
    }
