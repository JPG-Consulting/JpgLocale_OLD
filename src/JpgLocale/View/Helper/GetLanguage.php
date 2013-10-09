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
namespace JpgLocale\View\Helper;


use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Zend\View\Helper\AbstractHelper;

class GetLanguage extends AbstractHelper implements ServiceLocatorAwareInterface
{
	protected $serviceLocator;
	
	protected $language;
	
	public function __invoke()
	{
		if (empty($this->language)) {
			$locale = $this->serviceLocator->get('jpglocale_listener')->getLocale();
			if (extension_loaded('intl')) {
				$this->language = \Locale::getPrimaryLanguage($locale);
			} else {
				$locale = preg_replace('/\_/', '-', $locale);
				$locale = explode('-', $locale, 2);
				$this->language = $locale[0];
			}
		}
		
		return $this->language;
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
		return $this;
	}
	
	
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}
}