<?php
namespace Recognize\MailplusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
	Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Recognize\MailplusBundle\DependencyInjection
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('recognize_mailplus');
		$rootNode->children()
			->arrayNode('mailplus')
				->isRequired()
				->children()
					->variableNode('id')->isRequired()->end()
					->variableNode('password')->isRequired()->end()
					->arrayNode('urls')
						->isRequired()
						->children()
							->variableNode('contacts')->isRequired()->end()
							->variableNode('temporary_lists')->isRequired()->end()
							->variableNode('campaigns')->isRequired()->end()
						->end()
					->end()
				->end()
			->end()
		->end();

		return $treeBuilder;
	}

}