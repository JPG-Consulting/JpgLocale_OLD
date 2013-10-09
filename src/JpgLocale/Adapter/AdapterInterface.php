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

interface AdapterInterface
{

	/**
	 * Add a new locale
	 * 
	 * @param array|string|\JpgLocale\Locale\LocaleInterface $locale
	 */
	public function addLocale( $locale );
	
	/**
	 * Get all available locales
	 * 
	 * @return array Available locales
	 */
	public function getAvailableLocales();
	
	/**
	 * Get all active locales
	 * 
	 * @return array All active locales
	 */
	public function getActiveLocales();
	
	/**
	 * Get the default locale.
	 * 
	 * @return JpgLocale\Locale\LocaleInterface
	 */
	public function getDefaultLocale();
	
	/**
	 * Set the default locale.
	 * 
	 * @param string|JpgLocale\Locale\LocaleInterface $locale
	 */
	public function setDefaultLocale( $locale );
	
	/**
	 * Searches the active locales for the best match to the language
	 * 
	 * @param string|JpgLocale\Locale\LocaleInterface $locale
	 * @return JpgLocale\Locale\LocaleInterface
	 */
	public function lookup($locale);
	
	/**
	 * Set the adapter options
	 * 
	 * @param array $options The options for the adapter
	 */
	public function setOptions( $options = array() );
	
}