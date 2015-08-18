<?php

/**
 * Model Admin for FAQs search module.
 * Allows a content author to publish and edit questions and answers.
 * @see FAQ for FAQ DataObject.
 */
class FAQAdmin extends ModelAdmin {

	private static $url_segment = 'faq';

	private static $managed_models = array(
		'FAQ'
	);

	private static $menu_title = 'FAQs';

	/**
	 * Overload ModelAdmin->getExportFields() so that we can export keywords.
	 */
	public function getExportFields() {
		return array(
			'Question' => 'Question',
			'Answer' => 'Answer',
			'Keywords' => 'Keywords'
		);
	}
}
