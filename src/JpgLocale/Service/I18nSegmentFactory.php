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
namespace JpgLocale\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\ServiceManager\AbstractPluginManager;

use JpgLocale\Mvc\Router\Http\I18nSegment;

use Zend\ServiceManager\MutableCreationOptionsInterface;

use Zend\ServiceManager\FactoryInterface;

class I18nSegmentFactory implements FactoryInterface, MutableCreationOptionsInterface
{
/**
     * @var array
     */
    protected $creationOptions;

    /**
     * @param  array $creationOptions
     * @throws Exception\RuntimeException
     */
    public function setCreationOptions(array $creationOptions)
    {

        if (!isset($creationOptions['route'])) {
        	$creationOptions['route'] = '/[:locale/]';
        }
        

        $this->creationOptions = $creationOptions;
    }

    /**
     * {@inheritDoc}
     *
     * @return ResourceGraphRoute
     *
     * @throws RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator instanceof AbstractPluginManager) {
            //throw RuntimeException::pluginManagerExpected($serviceLocator);
            throw new \Exception('Not a plugin');
        }

        $parentLocator = $serviceLocator->getServiceLocator();

       // try {
       //     $resource = $parentLocator->get($this->creationOptions['resource']);
       // } catch (ServiceNotFoundException $exception) {
       //     //throw \RuntimeException::missingResource($this->creationOptions['resource'], $exception);
       //     throw new \Exception('Missing resource ' . $this->creationOptions['resource']);
       // }

        /* @var $metadataFactory \Metadata\MetadataFactoryInterface */
        //$metadataFactory = $parentLocator->get('ZfrRest\Resource\Metadata\MetadataFactory');

        $route = I18nSegment::factory($this->creationOptions);
        $route->setServiceManager( $parentLocator );
        return $route;
    }
}