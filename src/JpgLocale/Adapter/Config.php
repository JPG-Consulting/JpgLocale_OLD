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
namespace JpgLocale\Adapter;

use JpgLocale\Exception;
use JpgLocale\Locale\Locale;

class Config implements AdapterInterface
{

	protected $default;
	
	protected $locales = array();
	
	
	public function setOptions($options = array())
	{
		if (!is_array($options)) {
			throw new Exception\InvalidArgumentException('Options must be an array');
		}
		
		$options = array_change_key_case($options, CASE_LOWER);
		
		foreach($options as $option => $value) {
			switch ($option)
			{
				case 'default':
					$this->setDefaultLocale($option['default']);
					break;
				case 'locales':
					if ($value instanceof Traversable) {
			            $value = ArrayUtils::iteratorToArray($value);
			        } elseif (!is_array($value)) {
			            throw new Exception\InvalidArgumentException('Locales definition must be an array or Traversable object');
			        }
			        
			        foreach($value as $key => $data) {
			        	$data['locale'] = isset($data['locale']) ? $data['locale'] : $key;
			        	$this->addLocale($data);
			        }
			        
					break;
			}
		}
		
		// This handler expects all optionsto be set
		// So let's check for defaults
		if (empty($this->default)) {
			if (!extension_loaded('intl')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s component requires the intl PHP extension',
                    __NAMESPACE__
                ));
            }
            
            $defaultLocale = \Locale::getDefault();
            $this->setDefaultLocale($defaultLocale); 
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Adapter.AdapterInterface::addLocale()
	 */
	public function addLocale($locale)
	{
		if ($locale instanceof \JpgLocale\Locale\LocaleInterface) {
			$this->locales[$locale->getLocale()] = $locale;
		} elseif ($locale instanceof Traversable) {
			$locale = ArrayUtils::iteratorToArray($locale);
		} elseif (!is_array($locale)) {
			throw new Exception\InvalidArgumentException('Locale must be a LocaleInterface, array or Traversable object');
		}
		
		$this->locales[$locale['locale']] = $this->localeFromArray($locale);
		
		return $this;
	}
	
	protected function localeFromArray($specs = array())
	{
		if ($specs instanceof Traversable) {
            $specs = ArrayUtils::iteratorToArray($specs);
        } elseif (!is_array($specs)) {
            throw new Exception\InvalidArgumentException('Locale definition must be an array or Traversable object');
        }
        
		if (!isset($specs['locale'])) {
            throw new Exception\InvalidArgumentException('Missing "locale" option');
		}
		
		$objLocale = new Locale();
		$objLocale->setLocale($specs['locale'])
		          ->setEnglishName( isset($specs['english_name']) ? $specs['english_name'] : null )
		          ->setNativeName( isset($specs['native_name']) ? $specs['native_name'] : null )
		          ->setState(true);
		return $objLocale;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Adapter.AdapterInterface::getActiveLocales()
	 */
	public function getActiveLocales()
	{
		// In module.config.php we only configure active locales
		// therefore, in this case, active and available locales
		// are the same
		return $this->locales;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Adapter.AdapterInterface::getAvailableLocales()
	 */
	public function getAvailableLocales()
	{
		// In module.config.php we only configure active locales
		// therefore, in this case, active and available locales
		// are the same
		return $this->locales;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Adapter.AdapterInterface::getDefaultLocale()
	 */
	public function getDefaultLocale()
	{
		return $this->default;
	}

	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Adapter.AdapterInterface::setDefaultLocale()
	 */
	public function setDefaultLocale($locale)
	{
		if ($locale instanceof \JpgLocale\Locale\LocaleInterface) {
			$this->default = $locale;
		} elseif (is_string($locale)) {
			$locale = array('locale' => $locale);
		}
		
		$this->default = $this->localeFromArray($locale);
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Adapter.AdapterInterface::lookup()
	 */
	public function lookup($locale)
	{
		if ($locale instanceof JpgLocale\Locale\LocaleInterface) {
			$locale = $locale->getLocale();
		}
		
		if (extension_loaded('intl')) {
			$locales = array_keys($this->locales);
            $match = \Locale::lookup($locales, $locale);
        } else {
        	// No intl extension :(
        	// TODO: Make it more flexible
        	$match = $locale;
        }
        
        if (array_key_exists($match, $this->locales)) {
        	return $this->locales[$match];
        } else {
        	return $this->default;
        }
	}
	
}