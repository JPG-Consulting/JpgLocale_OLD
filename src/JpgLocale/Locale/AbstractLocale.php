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
namespace JpgLocale\Locale;

abstract class AbstractLocale implements LocaleInterface
{
	/**
	 * The locale
	 * 
	 * @var string
	 */
	protected $locale;

	/**
	 * The english display name
	 * 
	 * @var string
	 */
	protected $english_name;

	/**
	 * The native display name
	 * 
	 * @var string
	 */
	protected $native_name;

	/**
	 * The locale state
	 * 
	 * @var string
	 */
	protected $active;

	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Locale.LocaleInterface::getLocale()
	 */
	public function getLocale()
	{
		return $this->locale;
	}
	
	/**
	 * Set the locale
	 * 
	 * @param string $locale The locale
	 */
	public function setLocale( $locale )
	{
		$this->locale = $locale;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Locale.LocaleInterface::getEnglishName()
	 */
	public function getEnglishName()
	{
		if (empty($this->english_name)) {
			if (!extension_loaded('intl')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s component requires the intl PHP extension',
                    __NAMESPACE__
                ));
            }
            
            $this->english_name = \Locale::getDisplayLanguage( $this->locale, 'en');
		}
		return $this->english_name;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Locale.LocaleInterface::setEnglishName()
	 */
	public function setEnglishName($name)
	{
		$this->english_name = $name;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Locale.LocaleInterface::getNativeName()
	 */
	public function getNativeName()
	{
		if (empty($this->native_name)) {
			if (!extension_loaded('intl')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s component requires the intl PHP extension',
                    __NAMESPACE__
                ));
            }
            
            $this->native_name = \Locale::getDisplayLanguage( $this->locale, $this->locale);
		}
		return $this->native_name;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Locale.LocaleInterface::setNativeName()
	 */
	public function setNativeName($name)
	{
		$this->native_name = $name;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Locale.LocaleInterface::getState()
	 */
	public function getState()
	{
		return $this->active;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JpgLocale\Locale.LocaleInterface::setState()
	 */
	public function setState( $state )
	{
		$this->active = $state;
		return $this;
	}
}