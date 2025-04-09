<?php

namespace MediaWiki\Extension\Forms\Util;

use MediaWiki\Config\HashConfig;
use MediaWiki\Registration\ExtensionRegistry;

class TargetFactory {

	/**
	 * @param string $type
	 * @param array $data
	 * @return mixed|null
	 */
	public function makeTarget( string $type, array $data = [] ) {
		$targets = ExtensionRegistry::getInstance()->getAttribute(
			"FormsTargets"
		);

		if ( isset( $targets[$type] ) ) {
			$factory = $targets[$type];
			if ( is_callable( $factory ) ) {
				$id = $data['_id'] ?? $this->data['_id'] ?? null;
				if ( $id ) {
					$data['_id'] = $id;
				}
				unset( $data['type'] );
				$config = new HashConfig( array_merge(
					$data, [
						'form' => $data['form'] ?? null,
					]
				) );

				return call_user_func_array( $factory, [ $config ] );
			}
		}
		return null;
	}
}
