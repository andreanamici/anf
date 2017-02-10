<?php


/**
 * Usando questo trait si hanno accesso a tutti i metodo dei trait Trait_Application*, tranne che per gli hooks
 */
trait Trait_Application
{
   use Trait_ApplicationKernel,
           
       Trait_ApplicationCommands,
           
       Trait_ApplicationConfigs,
           
       Trait_ApplicationLanguages,
           
       Trait_ApplicationPlugins,
   
       Trait_ApplicationRouting,
           
       Trait_ApplicationServices;
}