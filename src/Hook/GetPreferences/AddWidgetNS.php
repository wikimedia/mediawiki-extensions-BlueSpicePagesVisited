<?php

namespace BlueSpice\PagesVisited\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddWidgetNS extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-pagesvisited-pref-widgetns'] = array(
			'type' => 'multiselectex',
			'label-message' => 'bs-pagesvisited-pref-widgetns',
			'section' => 'bluespice/pagesvisited',
			'options' => \BsNamespaceHelper::getNamespacesForSelectOptions( array( -2, NS_MEDIA, NS_MEDIAWIKI, NS_MEDIAWIKI_TALK, NS_SPECIAL ) )
			);
		return true;
	}
}
