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


use JpgLocale\Adapter\AdapterInterface;
use JpgLocale\Exception;
use Traversable;
use Zend\EventManager\EventManagerInterface;

use Zend\EventManager\ListenerAggregateInterface;

class LocaleListener implements ListenerAggregateInterface 
{

	/**
	 * The locale adapter instance
	 * 
	 * @var JpgLocale\Adapter\AdapterInterface
	 */
	protected $adapter;
	
	/**
	 * Event callbacks
	 * 
     * @var array
     */
    protected $callbacks = array();

    /**
     * The handlers we are using
     * Enter description here ...
     * @var array
     */
    protected $handlers = array();
    
    /**
     * Track if locale has been detected
     * 
     * @var bool
     */
    protected $localeDetected = false;
    
    protected $currentLocale;
    
    /**
     * Contructor.
     * 
     * @param array|Traversable $config
     */
    public function __construct( $options = array())
    {
    	$this->setOptions( $options );
    }
    
    /**
     * Set options
     * 
     * @param array $options The options
     */
    public function setOptions( $options = array())
    {
    
		foreach($options as $option => $value) {
			switch ($option) 
			{
				case 'adapter':
					$this->setAdapter($value, $options);
					break;
				case 'handlers':
					foreach ($value as $handler) {
						$this->addHandler($handler);
					}
					break;
			}
		}	
    }
    
    public function addHandler( $handler )
    {
    	if ($handler instanceof \JpgLocale\Handler\HandlerInterface) {
    		$this->handlers[] = $handler;
    	} elseif (is_string($handler)) {
	    	if (strpos('\\', $handler) === false) {
				$handlerObj = 'JpgLocale\\Handler\\' . $handler;
			} else {
				$handlerObj = $handler;
			}
			
			$this->handlers[] = new $handlerObj();
    	} else {
    		// TODO: throw exception
    	}
    	
    	return $this;
    }
    
    public function getLocale()
    {
    	if (!empty($this->currentLocale)) return $this->currentLocale;
    	return $this->adapter->getDefaultLocale()->getLocale();
    }
    
    /**
     * Retrieve the locale adapter interface
     * 
     * @return JpgLocale\Adapter\AdapterInterface The adapter
     */
    public function getAdapter()
    {
    	if (empty($this->adapter)) {
			throw new Exception\RuntimeException('No locale adapter available');
		}
    	return $this->adapter;
    }
    
    /**
     * Set the locale adapter
     * 
     * @param JpgLocale\Adapter\AdapterInterface $adapter The locale adapter
     * @throws Exception\InvalidArgumentException
     */
    public function setAdapter( $adapter)
    {
    	if ($adapter instanceof \JpgLocale\Adapter\AdapterInterface) {
    		$this->adapter = $adapter;
    		return $this;
    	} elseif ($adapter instanceof Traversable) {
			$adapter = ArrayUtils::iteratorToArray($adapter);
		} elseif (!is_array($adapter)) {
			throw new Exception\InvalidArgumentException('Adapter must be an array, Traversable object or an object implementing AdapterInterface');
		}
    	
		if (!isset($adapter['type'])) {
			throw new Exception\InvalidArgumentException('Missing "type" option');
		}
    	
    	if (strpos('\\', $adapter['type']) === false) {
			$adapterObj = 'JpgLocale\\Adapter\\' . $adapter['type'];
		} else {
			$adapterObj = $adapter['type'];
		}
		$this->adapter = new $adapterObj();
		
		if (isset($adapter['options'])) {
			$this->adapter->setOptions($adapter['options']);
		}
		
    	return $this;
    }
    
	/**
	 * (non-PHPdoc)
	 * @see Zend\EventManager.ListenerAggregateInterface::attach()
	 */
	public function attach(EventManagerInterface $events)
	{
		$this->listeners[] = $events->attach(\Zend\Mvc\MvcEvent::EVENT_ROUTE, array($this, 'detectLocale'), 0);
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend\EventManager.ListenerAggregateInterface::detach()
	 */
	public function detach(EventManagerInterface $events)
	{
		foreach ($this->callbacks as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->callbacks[$index]);
            }
        }
	}

	public function detectLocale(\Zend\Mvc\MvcEvent $e)
	{
		// Avoid trying to detect the locale if it has already been detected
		if (!$this->localeDetected) {
			// An adapter should have been setup by now
			if (empty($this->adapter)) {
				throw new Exception\RuntimeException('No locale adapter available');
			}
			
			foreach($this->handlers as $handler) {
				$locale = $handler->detect($e);
				if (!empty($locale)) {
					$locale = $this->adapter->lookup($locale);
					
					// Store the current locale
					// This is used by ViewHelpers
					$this->currentLocale = $locale;
					
					$sm = $e->getApplication()->getServiceManager();
					if ($sm->has('Translator')) {
						$sm->get('Translator')->setLocale( $locale->getLocale() );
						break;
					}
				}
			}
			
		}
	}
}