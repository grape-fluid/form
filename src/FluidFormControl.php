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
		$this->fluidForm = $fluidForm;
	}


	/**
	 * @param $defaultValues
	 * @param bool $mergeWithPrevious
	 */
	public function setDefaults($defaultValues, $mergeWithPrevious = false)
	{
		$this->fluidForm->setDefaultValues($defaultValues, $mergeWithPrevious);
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

		$reflection = new \ReflectionObject($this->fluidForm);
		$docComment = $reflection->getDocComment();
		$name       = '';
		$annot      = '';

		if ($docComment) {
			if (preg_match('/@name\s+(\S+)/', $docComment, $matches)) {
				$name = $matches[1]; ;// @todo check
			}

			if (preg_match('/@width\s+(\S+)/', $docComment, $matches)) {
				$annot = $matches[1]; ;// @todo check
			}
		}

		$this->template->formName = $this->name;

		if ($annot && is_int($annot)) {
			$this->template->formWidth = ($annot < 1 ? 1 : $annot);
		} else {
			$this->template->formWidth = 12;
		}

		$template = str_replace(".php", ".latte", $reflection->getFileName());

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