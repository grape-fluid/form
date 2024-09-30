<?php

namespace Grapesc\GrapeFluid\FluidFormControl;

use Grapesc\GrapeFluid\EventDispatcher;
use Grapesc\GrapeFluid\Extenders\IExtender;
use Grapesc\GrapeFluid\FluidTranslator;
use Grapesc\GrapeFluid\Logger;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;


/**
 * @author Kulíšek Patrik <kulisek@grapesc.cz>
 * @author Mira Jakes <jakes@grapesc.cz>
 */
abstract class FluidForm
{

	CONST EVENT_ON_SUBMIT               = "fluid.fluidForm.submit";
	CONST EVENT_ON_SUCCESS              = "fluid.fluidForm.success";
	CONST EVENT_ON_ERROR                = "fluid.fluidForm.error";
	CONST EVENT_BEFORE_BUILD            = "fluid.fluidForm.beforeBuild";
	CONST EVENT_AFTER_BUILD             = "fluid.fluidForm.afterBuild";
	CONST EVENT_AFTER_ADD_SUBMIT_BUTTON = "fluid.fluidForm.afterAddSubmitButton";
	CONST EVENT_ON_SET_DEFAULTS         = "fluid.fluidForm.setDefaults";

	/** @var Form */
	protected $form;

	/** @var EventDispatcher */
	protected $dispatcher;

	/** @var FluidTranslator */
	protected $translator;

	/** @var Logger */
	protected $logger;

	/** @var array */
	protected $parameters = [];

	/** @var int|null */
	protected $editId;

	/** @var int|null */
	protected $createdId;

	/** @var mixed */
	protected $defaultValues = null;

	/** @var bool */
	protected $autoFocus = true;


	/**
	 * FluidForm constructor.
	 * @param EventDispatcher $dispatcher
	 * @param FluidTranslator $translator
	 * @param Logger $logger
	 * @param Container $container
	 */
	public function __construct(EventDispatcher $dispatcher, FluidTranslator $translator, Logger $logger, Container $container)
	{
		$container->callInjects($this);
		$this->dispatcher = $dispatcher;
		$this->translator = $translator;
		$this->logger     = $logger;

		$class      = new \ReflectionClass($this);
		$this->form = new Form(null, $class->getShortName());
		$this->form->addGroup();

		foreach (array_keys($container->findByTag('fluid.extender.form')) AS $extender) {
			if ($container->hasService($extender)) {
				$service = $container->getService($extender);
				if ($service instanceof IExtender) {
					$service->register($dispatcher, get_called_class());
				}
			}
		}
	}


	/**
	 * for build form
	 */
	public function startup()
	{
		$this->onBeforeBuildEvent();

		$this->form->setTranslator($this->translator);

		$this->build($this->form);

		$this->onAfterBuildEvent();

		$buttonsGroup = $this->form->getGroup('buttons');
		if (!$buttonsGroup) {
			$this->form->addGroup('buttons');
		} else {
			$this->form->setCurrentGroup($buttonsGroup);
		}

		$this->addButtons($this->form);

		$this->onAfterAddSubmitButtonEvent();

		$this->onSetDefaults($this->form, $this);

		if ($defaultValues = $this->getDefaultValues()) {
			$this->form->setDefaults($defaultValues);
		}
	}


	/**
	 * Přetížením této metody je možné sestavit formulář
	 *
	 * @param Form $form
	 */
	protected function build(Form $form)
	{
	}


	/**
	 * @return string
	 */
	protected function getDefaultSubmitCaption()
	{
		return $this->isEditMode() ? $this->translator->translate('Uložit') : $this->translator->translate('Přidat');
	}


	/**
	 * @param Form $form
	 */
	protected function addButtons(Form $form)
	{
		if ($this->form->getComponent("submit", false) === null) {
			$form->addSubmit("submit", $this->getDefaultSubmitCaption());
		}
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	protected function submit(Control $control, Form $form)
	{
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	protected function afterSucceedSubmit(Control $control, Form $form)
	{
	}

	/**
	 * @param bool $asArray
	 * @return array|ArrayHash
	 */
	public function getValues($asArray = false)
	{
		//@todo event listener
		return $this->getForm()->getValues($asArray);
	}


	public function onBeforeBuildEvent()
	{
		$this->dispatcher->dispatch(new FluidFormEvent($this), self::EVENT_BEFORE_BUILD);
	}


	public function onAfterBuildEvent()
	{
		$this->dispatcher->dispatch(new FluidFormEvent($this), self::EVENT_AFTER_BUILD);
	}


	public function onAfterAddSubmitButtonEvent()
	{
		$this->dispatcher->dispatch(new FluidFormEvent($this), self::EVENT_AFTER_ADD_SUBMIT_BUTTON);
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	public function onSubmitEvent(Control $control, Form $form)
	{
		$this->dispatcher->dispatch(new FluidFormEvent($this), self::EVENT_ON_SUBMIT);
	}


	/**
	 * @param Form $form
	 * @param mixed $defaults
	 */
	public function onSetDefaults(Form $form, $defaults)
	{
		$this->dispatcher->dispatch(new FluidFormEvent($this, $defaults), self::EVENT_ON_SET_DEFAULTS);
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	public function onSuccessEvent(Control $control, Form $form)
	{
		$this->submit($control, $form);
		if ($form->isValid()) {
			$this->dispatcher->dispatch(new FluidFormEvent($this), self::EVENT_ON_SUCCESS);
			$this->afterSucceedSubmit($control, $form);
		}
	}


	/**
	 * @param Control $control
	 * @param Form $form
	 */
	public function onErrorEvent(Control $control, Form $form)
	{
		if ($control->getPresenter()->isAjax()) {
			$control->redrawControl('errors');
		}

		$this->dispatcher->dispatch(new FluidFormEvent($this), self::EVENT_ON_ERROR);
	}


	/**
	 * @return Form
	 */
	public function getForm()
	{
		return $this->form;
	}


	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}


	/**
	 * @param array $params
	 */
	public function setParameters(array $params)
	{
		$this->parameters = $params;
	}


	/**
	 * @param $parameter
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getParameter($parameter, $defaultValue = null)
	{
		return array_key_exists($parameter, $this->parameters) ? $this->parameters[$parameter] : $defaultValue;
	}


	/**
	 * @return int|null
	 */
	public function getCreatedId()
	{
		return $this->createdId;
	}


	/**
	 * @param int $id
	 */
	public function setEditId($id)
	{
		$this->editId = $id;
	}


	/**
	 * @return int|null
	 */
	public function getEditId()
	{
		return $this->editId;
	}


	/**
	 * @return bool
	 */
	public function isEditMode()
	{
		$values = $this->getForm()->getValues(true);
		return (boolean) $this->editId || (isset($values['id']) AND $values['id'] !== "");
	}


	/**
	 * @param mixed|null $defaultValues
	 * @param bool $mergeWithPrevious
	 */
	public function setDefaultValues($defaultValues = null, $mergeWithPrevious = false)
	{
		if (!is_null($defaultValues)) {
			if (is_object($defaultValues) && $defaultValues instanceof \Traversable) {
				$defaultValues = iterator_to_array($defaultValues);
			} elseif(!is_array($defaultValues)) {
				throw new \InvalidArgumentException("Default values must be array or \\Traversable, " . gettype($defaultValues) . " given.");
			}

			if (!$this->getEditId() AND isset($defaultValues['id'])) {
				$this->setEditId($defaultValues['id']);
			}

			$this->defaultValues = ($mergeWithPrevious && is_array($this->defaultValues)) ? array_merge($this->defaultValues, $defaultValues) : $defaultValues;
		}
	}


	/**
	 * @return mixed
	 */
	public function getDefaultValues()
	{
		return $this->defaultValues;
	}


	/**
	 * @param $key
	 * @param null $default
	 * @return null
	 */
	public function getDefaultValue($key, $default = null)
	{
		if (isset($this->getDefaultValues()[$key])) {
			return $this->getDefaultValues()[$key];
		}

		return $default;
	}


	public function enableAutoFocus()
	{
		$this->autoFocus = true;
	}


	public function disableAutoFocus()
	{
		$this->autoFocus = false;
	}


	/**
	 * @return bool
	 */
	public function getAutoFocus()
	{
		return $this->autoFocus;
	}

}