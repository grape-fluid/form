<?php

namespace Grapesc\GrapeFluid\FluidFormControl;

use Grapesc\GrapeFluid\EventDispatcher;
use Grapesc\GrapeFluid\FluidTranslator;
use Grapesc\GrapeFluid\Logger;
use Nette\Application\Application;
use Nette\DI\Container;


/**
 * @author Jiri Novy <novy@grapesc.cz>
 */
class FluidFormFactory
{

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var FluidTranslator */
	private $translator;

	/** @var Logger */
	private $logger;

	/** @var Container */
	private $container;


	/**
	 * FluidFormFactory constructor.
	 * @param EventDispatcher $dispatcher
	 * @param FluidTranslator $translator
	 * @param Logger $logger
	 * @param Container $container
	 */
	public function __construct(EventDispatcher $dispatcher, FluidTranslator $translator, Logger $logger, Container $container)
	{
		$this->dispatcher = $dispatcher;
		$this->translator = $translator;
		$this->logger     = $logger;
		$this->container  = $container;
	}


	/**
	 * @param string $formClass
	 * @param bool $returnControl
	 * @return FluidForm|FluidFormControl
	 */
	public function create($formClass, $returnControl = true)
	{
		$reflection = new \ReflectionClass($formClass);
		if ($reflection->isSubclassOf(FluidForm::class)) {
			/** @var FluidForm $fluidForm */
			$fluidForm = $reflection->newInstance($this->dispatcher, $this->translator, $this->logger, $this->container);
			$fluidForm->setParameters($this->container->getByType(Application::class)->getPresenter()->getParameters());
			$this->container->callInjects($fluidForm);

			if ($returnControl) {
				return new FluidFormControl($fluidForm);
			}

			return $fluidForm;
		} else {
			throw new \InvalidArgumentException("Is not type of FluidForm");
		}
	}

}