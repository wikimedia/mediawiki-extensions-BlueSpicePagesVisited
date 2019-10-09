<?php

namespace BlueSpice\PagesVisited\Panel;

use Title;
use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Panel\BasePanel;
use BlueSpice\Data\ResultSet;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\Filter;
use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Data\Filter\StringValue;
use BlueSpice\WhoIsOnline\Data\Record;
use BlueSpice\PagesVisited\Data\Store;
use BlueSpice\Calumma\Components\SimpleLinkListGroup;
use BsStringHelper;

class PagesVisited extends BasePanel implements IPanel {
	protected $params = [];

	/**
	 *
	 * @param \SkinTemplate $sktemplate
	 * @param array $params
	 * @return \self
	 */
	public static function factory( $sktemplate, $params ) {
		return new self( $sktemplate, $params );
	}

	/**
	 *
	 * @param \SkinTemplate $skintemplate
	 * @param array $params
	 * @return \self
	 */
	public function __construct( $skintemplate, $params ) {
		parent::__construct( $skintemplate );
		$this->params = $params;
	}

	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-pagesvisited-widget-title' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$recordSet = new ResultSet( [], 0 );
		if ( !$this->skintemplate->getSkin()->getUser()->isAnon() ) {
			$readerParams = new ReaderParams( $this->makeParams() );
			$recordSet = ( new Store() )->getReader()->read( $readerParams );
		}

		// Dumb default
		$maxTitleLength = 20;
		if ( isset( $this->params['maxtitlelength'] ) ) {
			$maxTitleLength = (int)$this->params['maxtitlelength'];
		}

		$links = [];
		if ( $recordSet->getTotal() > 0 ) {
			foreach ( $recordSet->getRecords() as $record ) {
				$title = Title::makeTitle(
					$record->get( Record::PAGE_NAMESPACE ),
					$record->get( Record::PAGE_TITLE )
				);
				if ( !$title ) {
					continue;
				}
				$display = BsStringHelper::shorten( $title->getPrefixedText(), [
					'max-length' => $maxTitleLength,
					'position' => 'middle'
				] );
				$link = [
					'href' => $title->getFullURL(),
					'text' => $display,
					'title' => $title->getPrefixedText(),
					'classes' => ' bs-usersidebar-internal '
				];
				$links[] = $link;
			}
		}

		$linkListGroup = new SimpleLinkListGroup( $links );

		return $linkListGroup->getHtml();
	}

	/**
	 *
	 * @return array
	 */
	protected function makeParams() {
		$params = [
			ReaderParams::PARAM_LIMIT => 7,
			ReaderParams::PARAM_FILTER => [],
			ReaderParams::PARAM_FILTER => [ [
				Filter::KEY_COMPARISON => StringValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::ACTION,
				Filter::KEY_VALUE => 'view',
				Filter::KEY_TYPE => FieldType::STRING
			], [ Filter::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::USER_ID,
				Filter::KEY_VALUE => (int)$this->skintemplate->getSkin()->getUser()->getId(),
				Filter::KEY_TYPE => 'numeric'
			] ]
		];
		if ( !empty( $this->params['namespaces'] ) ) {
			$params[ReaderParams::PARAM_FILTER][] = [
				Filter::KEY_COMPARISON => ListValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::PAGE_NAMESPACE,
				Filter::KEY_VALUE => $this->params['namespaces'],
				Filter::KEY_TYPE => FieldType::LISTVALUE
			];
		}
		if ( !empty( $this->params['count'] ) ) {
			$params[ReaderParams::PARAM_LIMIT] = $this->params['count'];
		}

		return $params;
	}
}
