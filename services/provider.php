<?php \defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Fields\RadicalMultiField\Extension\RadicalMultiField;

return new class implements ServiceProviderInterface {

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->set(PluginInterface::class,
			function (Container $container) {
				$plugin  = PluginHelper::getPlugin('fields', 'radicalmultifield');
				$subject = $container->get(DispatcherInterface::class);

				$plugin = new RadicalMultiField($subject, (array) $plugin);
				$plugin->setApplication(Factory::getApplication());
				$plugin->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));

				return $plugin;
			}
		);
	}
};
