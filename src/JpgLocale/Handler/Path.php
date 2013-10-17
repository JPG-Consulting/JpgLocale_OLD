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
namespace JpgLocale\Handler;

use Zend\Mvc\MvcEvent;

class Path extends AbstractHandler
{
	protected $regex = '([a-z]{2}){1}(\-[a-zA-Z]{2,4}){0,1}';

	public function setOptions( $options = array())
	{
		parent::setOptions($options);
		
		if (isset($options['regex'])) $this->setRegex($options['regex']);
		
		return $this;
	}
	
	/**
	 * Set the regular expresion for locales
	 * 
	 * @param string $regex
	 */
	public function setRegex( $regex )
	{
		if (empty($regex)) $regex = '([a-z]{2}){1}(\-[a-zA-Z]{2,4}){0,1}';
		$this->regex = $regex;
		
		return $this;
	}
	
	public function detect(MvcEvent $e)
	{
		$request = $e->getRequest();
		if (!method_exists($request, 'getUri')) {
            return null;
        }
        
        $uri  = $request->getUri();
        $path = $uri->getPath();
        
        $result = preg_match('(^/' . $this->regex . '/)', $path, $matches);
		if (!$result) {
            return null;
        }
        
        // We have a match!
        // Do some magic to set the route so it matches our config
        $path = ltrim($path, '/');
        $path = explode('/', $path, 2);
        $path = '/' . $path[1];
        $e->getRequest()->getUri()->setPath($path);
        
        
        return $matches[1];
	}
	
}