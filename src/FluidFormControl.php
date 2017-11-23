<?php

namespace Grapesc\GrapeFluid\FluidFormControl;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;


/**
 * @author Kulíšek Patrik <kulisek@grapesc.cz>
 */
class FluidFormControl extends Control
{

	/** @var FluidForm */
	private $fluidForm;


	public function __construct(FluidForm $fluidForm)
	{
		parent::__construct();
		$this->fluidForm = $fluidForm;
	}


	/**
	 * @param $defaultValues
	 */
	public function setDefaults($defaultValues)
	{
		$this->fluidForm->setDefaultValues($defaultValues);
	}


	/**
	 * @param string $name
	 * @return Form
	 */
	protected function createComponentFluidForm($name)
	{
		$this->fluidForm->startup();

		$this->fluidForm->getForm()->onSubmit[]  = [$this, 'onSubmit'];
		$this->fluidForm->getForm()->onSuccess[] = [$this, 'onSuccess'];
		$this->fluidForm->getForm()->onError[]   = [$this, 'onError'];

		return $this->fluidForm->getForm();
	}


	/**
	 * @param Form $form
	 */
	public function onSubmit(Form $form)
	{
		$this->fluidForm->onSubmitEvent($this, $form);
	}


	/**
	 * @param Form $form
	 */
	public function onSuccess(Form $form)
	{
		$this->fluidForm->onSuccessEvent($this, $form);
	}


	/**
	 * @param Form $form
	 */
	public function onError(Form $form)
	{
		$this->fluidForm->onErrorEvent($this, $form);
	}


	public function render()
	{
		$this->template->fluidForm = (__DIR__ . "/FluidForm.latte");

		$class = new \Nette\Reflection\ClassType($this->fluidForm);

		$this->template->formName = $class->hasAnnotation("name") ? $class->getAnnotation("name") : "";

		if ($class->hasAnnotation("width") && is_int($annot = $class->getAnnotation("width"))) {
			$this->template->formWidth = ($annot < 1 ? 1 : $annot);
		} else {
			$this->template->formWidth = 12;
		}

		$template = str_replace(".php", ".latte", $class->getFileName());

		if (file_exists($template)) {
			$this->template->setFile($template);
		} else {
			$this->template->setFile($this->template->fluidForm);
		}

		$this->template->render();
	}


	/**
	 * @return Form
	 */
	public function getForm()
	{
		return $this->fluidForm->getForm();
	}


	/**
	 * @return FluidForm
	 */
	public function getFluidForm()
	{
		return $this->fluidForm;
	}

}