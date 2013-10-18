<?php
/**
 * JpgLocale
 * 
 * Multilingual module for ZF-2
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *  
 * @author Juan Pedro Gonzalez Gutierrez
 * @copyright Copyright (c) 2013 Juan Pedro Gonzalez Gutierrez (http://www.jpg-consulting.com)
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 License
 */
namespace JpgLocale;

use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

use Zend\ModuleManager\Feature\RouteProviderInterface;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements 
	AutoloaderProviderInterface, 
	ConfigProviderInterface,
	RouteProviderInterface,
	ServiceProviderInterface,
	ViewHelperProviderInterface
{

	public function onBootstrap(\Zend\Mvc\MvcEvent $e)
	{
		$app = $e->getApplication();
		$sm  = $app->getServiceManager();
		
		$localeListener = $sm->get('jpglocale_listener');
		$localeListener->attach( $app->getEventManager() );
	}
	
	public function getAutoloaderConfig()
	{
		return array(
            \Zend\Loader\AutoloaderFactory::STANDARD_AUTOLOADER => array(
                \Zend\Loader\StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
	}
	
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getRouteConfig()
	{
		return array(
			'factories' => array(
				'I18nSegment' => 'JpgLocale\Service\I18nSegmentFactory'
			)
		);
	}
	
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'jpglocale_listener' => 'JpgLocale\Service\ListenerFactory'
			)
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'getLanguage' => function($helperPluginManager) {
					$serviceLocator = $helperPluginManager->getServiceLocator();
					$viewHelper = new View\Helper\GetLanguage();
					$viewHelper->setServiceLocator($serviceLocator);
					return $viewHelper;
				},
			)
		);
	}
}