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
namespace JpgLocale\Mvc\Router\Http;


use Zend\Mvc\Router\Http\RouteMatch;

use Zend\ServiceManager\ServiceManager;

use Zend\Mvc\Router\Http\RouteInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Stdlib\RequestInterface as Request;



class I18nSegment implements RouteInterface, ServiceManagerAwareInterface
{

	/**
	 * The service manager instrance
	 * 
	 * @var ServiceManager
	 */
	protected $serviceManager;
	
	/**
	 * The locale adapter
	 * 
	 * @var \JpgLocale\Adapter\AdapterInterface
	 */
	protected $localeAdapter;
	
	/**
	 * The parameter to use for locale
	 * 
	 * @var string
	 */
	protected $localeParameter = 'locale';
	
	/**
	 * Regular expresion for locale detection
	 * 
	 * @var string
	 */
	protected $regex = '([a-z]{2}){1}(\_[a-zA-Z]{2,4}){0,1}';
	
	/**
     * Default values.
     *
     * @var array
     */
    protected $defaults;
    
    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = array();

    
    public function __construct($defaults = array())
    {
    	$this->defaults = $defaults;
    }
    
	/**
	 * Get the locale adapter
	 * 
	 * @return \JpgLocale\Adapter\AdapterInterface
	 */
	public function getLocaleAdapter()
	{
		if (empty($this->localeAdapter)) {
			$this->localeAdapter = $this->serviceManager->get('jpglocale_listener')->getAdapter();
		}
		return $this->localeAdapter;
	}
    
    /**
     * (non-PHPdoc)
     * @see Zend\ServiceManager.ServiceManagerAwareInterface::setServiceManager()
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
    	$this->serviceManager = $serviceManager;
    	
    	// Set some defaults
    	$this->defaults[$this->localeParameter] = $this->getLocaleAdapter()->getDefaultLocale()->getLocale();
    	
    	return $this;
    }
    
    /**
     * Create a new route with given options.
     *
     * @see Zend\Mvc\Router.RouteInterface::factory()
     * @param  array|\Traversable $options
     * @return void
     */
    public static function factory($options = array())
    {
    	
    	if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }
        
    	// TODO: Implement
    	return new static($options['defaults']);
    }

    /**
     * Match a given request.
     *
     * @see Zend\Mvc\Router.RouteInterface::match()
     * @param  Request $request
     * @param  int $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
    	if (!method_exists($request, 'getUri')) {
            return null;
        }
        
        $uri  = $request->getUri();
        $path = $uri->getPath();
        
        $trimmed_path = trim($path, '/');
        if (empty($trimmed_path)) {
        	// The locale is not set, therefore set defaults
        	$localeService = $this->serviceManager->get('jpglocale_listener');
        	$locale = $localeService->getLocale()->getLocale();
        	
        	$path = '/' . $locale . '/';
        	$uri->setPath($path);
        }
        
    	if ($pathOffset !== null) {
            $result = preg_match('(\G/(' . $this->regex . ')/)', $path, $matches, null, $pathOffset);
        } else {
            $result = preg_match('(^/(' . $this->regex . ')/$)', $path, $matches);
        }
        
        if (!$result) {
        	// We are on the default locale!
        	$localeService = $this->serviceManager->get('jpglocale_listener');
        	$locale = $localeService->getLocale()->getLocale();
        	
        	$path = '/' . $locale . $path;
        	$uri->setPath($path);
        	
        	// Try again
	        if ($pathOffset !== null) {
	            $result = preg_match('(\G/(' . $this->regex . ')/)', $path, $matches, null, $pathOffset);
	        } else {
	            $result = preg_match('(^/(' . $this->regex . ')/$)', $path, $matches);
	        }
        	
	        // That's all folks. 
	        // If we re not sucesfull by now there is no match
	        if (!$result) {
            	return null;
	        }
        }
        
        $locale = $matches[1];
        $matchedLength = strlen($matches[0]);
        $matches = array($this->localeParameter => $locale);
        
        // check we have an active locale
        $lookup_locale = $this->getLocaleAdapter()->lookup($locale)->getLocale();
        if (strcasecmp($lookup_locale, $locale)) {
        	return null;
        }
        
        // Got it... Set it!
        $localeService = $this->serviceManager->get('jpglocale_listener');
        $localeService->setLocale($locale);
        
        return new RouteMatch(array_merge($this->defaults, $matches), $matchedLength);
    }

    /**
     * Assemble the route.
     *
     * @see Zend\Mvc\Router.RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {	
    	if (!isset($params[$this->localeParameter])) {
    		$localeService = $this->serviceManager->get('jpglocale_listener');
        	$params[$this->localeParameter] = $localeService->getLocale()->getLocale();
    	}
    	$mergedParams          = array_merge($this->defaults, $params);
    	
    	if (strcasecmp($params[$this->localeParameter], $this->defaults[$this->localeParameter])) {
    		$this->assembledParams = array($this->localeParameter);
    		return '/' . $mergedParams[$this->localeParameter] . '/';
    	}
    	
    	// Default value... default route
    	return '/';
    }
    
    
    /**
     * Get a list of parameters used while assembling.
     * 
     * @see Zend\Mvc\Router\Http.RouteInterface::getAssembledParams()
     * @return array
     */
    public function getAssembledParams()
    {
    	return $this->assembledParams;
    }
}