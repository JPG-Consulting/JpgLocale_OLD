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
use JpgLocale\Event\LocaleEvent;
use Traversable;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class LocaleListener implements EventsCapableInterface, ListenerAggregateInterface, ServiceManagerAwareInterface 
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
     * @var EventManagerInterface
     */
    protected $events;

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
    
    /**
     * Current locale
     * 
     * @var LocaleInterface
     */
    protected $currentLocale;
    
    /**
     * The service manager instance
     * 
     * @var ServiceManager
     */
    protected $serviceManager;
    
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
    
    /**
     * Add a handler
     * 
     * @param string|array|Traversable\JpgLocale\Handler\HandlerInterface $handler
     */
    public function addHandler( $handler )
    {
    	if ($handler instanceof \JpgLocale\Handler\HandlerInterface) {
    		$this->handlers[] = $handler;
    	} else {
    		$this->handlers[] = $this->factoryHandler($handler);
    	}
		
    	return $this;
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
    	} else {
    		$this->adapter = $this->factoryAdapter($adapter);
    	}
    	
    	return $this;
    }
    
    /**
     * Get the current locale.
     * 
     * @return \JpgLocale\Locale\LocaleInterface
     */
	public function getLocale()
    {
    	if (!empty($this->currentLocale)) return $this->currentLocale;
    	return $this->adapter->getDefaultLocale();
    }
    
    /**
     * Set the current locale
     * 
     * @param string|\JpgLocale\Locale\LocaleInterface $locale The locale
     */
    public function setLocale( $locale )
    {
    	if (is_string($locale)) {
    		$locale = $this->adapter->lookup($locale);
    	}
    	
    	// Save it
    	$this->currentLocale = $locale;
    	
    	// Change the translator locale
    	if ($this->serviceManager->has('Translator')) {
    		$translator = $this->serviceManager->get('Translator');
    		$translator->setLocale( $this->currentLocale->getLocale() );
    	}
    	
    	// Trigger an event to warn about the locale change
    	$localeEvent = new LocaleEvent();
    	$localeEvent->setLocale($this->currentLocale);
    	$this->getEventManager()->trigger(LocaleEvent::EVENT_LOCALE_CHANGE, $this, $localeEvent);
    }
    
	/**
	 * (non-PHPdoc)
	 * @see Zend\EventManager.ListenerAggregateInterface::attach()
	 */
	public function attach(EventManagerInterface $events)
	{
		$this->listeners[] = $events->attach(\Zend\Mvc\MvcEvent::EVENT_ROUTE, array($this, 'detectLocale'), 10000);
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
					$this->setLocale($locale);
					break;
				}
			}
			
		}
	}
	
	/**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
	public function  setServiceManager(ServiceManager $serviceManager)
	{
		$this->serviceManager = $serviceManager;
		return $this;
	}
	
 	/**
     * Set the event manager instance used by this module manager.
     *
     * @param  EventManagerInterface $events
     * @return ModuleManager
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
            'locale_manager',
        ));
        $this->events = $events;
        //$this->attachDefaultListeners();
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }
    
    /**
	 * Create an adapter from configuration data
	 * 
	 * @param array|Traversable $specs  
	 * @return \JpgLocale\Adapter\AdapterInterface
     */
    protected function factoryAdapter($specs)
	{
		if ($specs instanceof Traversable) {
			$specs = ArrayUtils::iteratorToArray($adapter);
		} elseif (!is_array($specs)) {
			throw new Exception\InvalidArgumentException('Adapter must be an array or Traversable object');
		}
		
		// Mandatory parameters
		if (!isset($specs['type'])) {
			throw new Exception\InvalidArgumentException('Missing "type" option');
		}
		
		// Full adapter type
		if (strpos('\\', $specs['type']) === false) {
			$adapter = '\\JpgLocale\\Adapter\\' . $specs['type'];
		} else {
			$adapter = $specs['type'];
		}
		
		// Create the adapter
		$adapter = new $adapter();
		// Sanity check
		if (!$adapter instanceof AdapterInterface) {
			throw new Exception\InvalidArgumentException('Adapter must implement AdapterInterface');
		}
		
		// Options
		if (isset($specs['options']) && method_exists($adapter, 'setOptions')) {
			$adapter->setOptions($specs['options']);
		}
		
		// finally return the adapter
		return $adapter;
	}
	
	/**
	 * Create a Handler from configuration data
	 * 
	 * @param array|string|Traversable $specs  
	 * @return \JpgLocale\Handler\HandlerInterface
     */
	protected function factoryHandler($specs)
	{
		if (is_string($specs)) {
    		$specs = array('type' => $specs);
    	} elseif ($specs instanceof Traversable) {
			$specs = ArrayUtils::iteratorToArray($specs);
		} elseif (!is_array($specs)) {
			throw new Exception\InvalidArgumentException('Handler must be an array, string or Traversable object');
		}

		if (!isset($specs['type'])) {
			throw new Exception\InvalidArgumentException('Missing "type" option');
		}
		
		// Full handler type
		if (strpos('\\', $specs['type']) === false) {
			$handler = '\\JpgLocale\\Handler\\' . $specs['type'];
		} else {
			$handler = $specs['type'];
		}
			
		// Create the handler object
		$handler = new $handler();
		// Sanity check
		if (!$handler instanceof \JpgLocale\Handler\HandlerInterface) {
			throw new Exception\InvalidArgumentException('Handler must implement HandlerInterface');
		}
		
		if (isset($specs['options'])) {
			$handlerObj->setOptions( $handler['options'] );			
		}
		
		// Finally return the handler
		return $handler;
	}
}