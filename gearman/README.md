# SilverStripe Gearman module

Adds a basic level of support for the gearmand php job queue

## Installation

* Install gearmand 
* This uses the bundled Net_Gearman library for communicating with gearman;
  it's a bit of a nightmare getting the pecl extension sorted out with 
  all dependencies atm!

## Usage

* Define a class that implements GearmanHandler
* The 'getName' method should return the name of a method on the class that
  will handle the processing of the job (exampleMethod)
* Start the listener by calling php gearman/gearman_runner.php
* Trigger the job by calling $this->gearmanService->exampleMethod();
* Any params passed through to exampleMethod are passed on to the worker
* Note: This only supports 'background' jobs at the moment, so there are NO
  return values
