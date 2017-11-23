<?php

namespace Grapesc\GrapeFluid\FluidFormControl;


/**
 * @author Kulíšek Patrik <kulisek@grapesc.cz>
 */
class FluidFormEventListener
{

	public function submit(FluidFormEvent $event)
	{
		$event->getFluidForm()->getLogger()->info("FluidForm submitted by " . get_called_class());
	}

	public function success(FluidFormEvent $event)
	{
		$event->getFluidForm()->getLogger()->info("FluidForm succeeded by " . get_called_class());
	}

	public function error(FluidFormEvent $event)
	{
		$event->getFluidForm()->getLogger()->info("FluidForm error in " . get_called_class());
	}

}