<?php

namespace BlueSpice\PagesVisited\Tag;

use BlueSpice\PagesVisited\Data\Store;
use BlueSpice\PagesVisited\Renderer\PageList;
use BlueSpice\Renderer\Params;
use BlueSpice\RendererFactory;
use BlueSpice\WhoIsOnline\Data\Record;
use MediaWiki\Context\RequestContext;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DataStore\FieldType;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\Filter\ListValue;
use MWStake\MediaWiki\Component\DataStore\Filter\Numeric;
use MWStake\MediaWiki\Component\DataStore\Filter\StringValue;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Sort;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class PagesVisitedHandler implements ITagHandler {

	public function __construct(
		private readonly RendererFactory $rendererFactory,
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$context = RequestContext::getMain();
		if ( !$parser->getUserIdentity()->isRegistered() ) {
			return $context->msg( 'bs-pagesvisited-label-anon-user' )->text();
		}

		$readerParams = new ReaderParams( $this->makeParams( $params, $parser->getUserIdentity() ) );
		$recordSet = ( new Store() )->getReader()->read( $readerParams );

		$portlet = $this->rendererFactory->get(
			'pagesvisited-pagelist',
			new Params( [
				PageList::PARAM_RECORD_SET => $recordSet,
				PageList::PARAM_MAX_TITLE_LENGTH
				=> $params['maxtitlelength']
			] ),
			$context
		);

		return $portlet->render();
	}

	/**
	 *
	 * @return array
	 */
	protected function makeParams( array $params, UserIdentity $userIdentity ) {
		$params = [
			ReaderParams::PARAM_LIMIT => $params['count'],
			ReaderParams::PARAM_FILTER => [],
			ReaderParams::PARAM_FILTER => [ [
				Filter::KEY_COMPARISON => StringValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::ACTION,
				Filter::KEY_VALUE => 'view',
				Filter::KEY_TYPE => FieldType::STRING
			], [ Filter::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::USER_ID,
				Filter::KEY_VALUE => $userIdentity->getId(),
				Filter::KEY_TYPE => 'numeric'
			] ]
		];
		if ( !empty( $params['namespaces'] ) ) {
			$params[ReaderParams::PARAM_FILTER][] = [
				Filter::KEY_COMPARISON => ListValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::PAGE_NAMESPACE,
				Filter::KEY_VALUE => $params['namespaces'],
				Filter::KEY_TYPE => FieldType::LISTVALUE
			];
		}
		$params[ReaderParams::PARAM_LIMIT] = ReaderParams::LIMIT_INFINITE;
		if ( !empty( $params['count'] ) ) {
			$params[ReaderParams::PARAM_LIMIT] = $params['count'];
		}
		if ( $params['order'] === 'pagename' ) {
			$params[ReaderParams::PARAM_SORT][] = [
				'property' => Record::PAGE_TITLE,
				'direction' => Sort::ASCENDING,
			];
		}
		return $params;
	}
}
