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

interface LocaleInterface
{

	/**
	 * Get the locale.
	 * 
	 * @return string The locale
	 */
	public function getLocale();
	
	/**
	 * Get the english display name for the current locale
	 * 
	 * @return string English display name of the language.
	 */
	public function getEnglishName();
	
	/**
	 * Set the english display name for the current locale
	 * 
	 * @param string $name English display name of the language.
	 */
	public function setEnglishName( $name );
	
	/**
	 * Get the native display name for the current locale
	 * 
	 * @return string Native display name of the language.
	 */
	public function getNativeName();
	
	/**
	 * Set the native display name of the current locale.
	 * 
	 * @param string $name Native display name of the language.
	 */
	public function setNativeName( $name );
	
	/**
	 * Get the current state of the locale
	 * 
	 * @return bool True if the locale is active or false if it is not.
	 */
	public function getState();
	
	/**
	 * Set the current state of the locale.
	 * 
	 * @param bool $state
	 */
	public function setState( $state );
	
}