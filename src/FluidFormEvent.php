<?php

namespace Grapesc\GrapeFluid\FluidFormControl;

use Nette\Application\UI\Form;
use Nette\ArrayHash;
use Nette\Database\IRow;
use Symfony\Contracts\EventDispatcher\Event;


/**
 * @author Kulíšek Patrik <kulisek@grapesc.cz>
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class FluidFormEvent extends Event
{

	/** @var FluidForm */
	private $form;

	/** @var null|array|\Traversable|IRow */
	private $defaults;


	/**
	 * FluidFormEvent constructor.
	 * @param FluidForm $form
	 * @param null|array|\Traversable|IRow $defaults
	 */
	public function __construct(FluidForm $form, $defaults = null)
	{
		$this->form     = $form;
		$this->defaults = $defaults;
	}


	/**
	 * @return FluidForm
	 */
	public function getFluidForm()
	{
		return $this->form;
	}


	/**
	 * @return Form
	 */
	public function getForm()
	{
		return $this->getFluidForm()->getForm();
	}


	/**
	 * @param boolean $asArray
	 * @return array|ArrayHash
	 */
	public function getValues($asArray = false)
	{
		return $this->getFluidForm()->getValues($asArray);
	}


	/**
	 * @return array|IRow|null|\Traversable
	 */
	public function getDefaults()
	{
		return $this->defaults;
	}

}