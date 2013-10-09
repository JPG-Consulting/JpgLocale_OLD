<?php
namespace JpgLocale\Event;

use Zend\EventManager\Event;

class LocaleEvent extends Event
{

	const EVENT_LOCALE_CHANGE = 'localeChange';
	
	protected $locale;
	
	public function getLocale()
	{
		return $this->locale;
	}
	
	public function setLocale( $locale )
	{
		$this->setParam('locale', $locale);
		$this->locale = $locale;
		return $this;
	}
}