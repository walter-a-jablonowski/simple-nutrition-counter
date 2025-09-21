<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

trait CombinedModel
{
  protected SimpleData $combinedModel;  // foods and supplements


  /*@
  
  makeCombinedModel()
  
  */
  private function makeCombinedModel()  /*@*/
  {
    $user = User::current();

    $this->combinedModel = new SimpleData();

    $dir = "data/bundles/Default_$user->id/foods";

    foreach( scandir($dir) as $file )
    {
      if( in_array( $file, ['.', '..']) || in_array( $file[0], ['_']) || ( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$dir/$file")))
        continue;

      $name = is_dir("$dir/$file")  ?  $file  :  pathinfo($file, PATHINFO_FILENAME);
      $food = is_file("$dir/$file")
            ? Yaml::parse( file_get_contents("$dir/$file"))
            : Yaml::parse( file_get_contents("$dir/$file/-this.yml"));

      $food['category'] = 'F';

      // Merge nutrients from food file (prio) over default foods
      // TASK: maybe we want to add at least an empty key if a type of nutrients is missing

      if( isset( $food['type']) && file_exists("data/food_defaults/$food[type].yml"))
      {
        $nutrients = Yaml::parse( file_get_contents("data/food_defaults/$food[type].yml"));

        foreach( self::NUTRIENT_GROUPS as $groupName )
        {
          if( isset( $nutrients[$groupName] ))
            $food[$groupName] = array_merge( $nutrients[$groupName], $food[$groupName] ?? []);
          // else
          //   // $food[$groupName] = $nutrients[$groupName];
          //   $food[$groupName] = $food[$groupName] ?? [];
        }

        // Duplicate salt and fibre (food value more precise, overrides food default)

        if( isset($food['nutritionalValues']['fibre']))
          $food['carbs']['Fibre'] = $food['nutritionalValues']['fibre'];

        $food['minerals']['Salt'] = $food['nutritionalValues']['salt'];
      }

      $this->combinedModel->set( $name, $food );
    }

    $dir = "data/bundles/Default_$user->id/supplements";

    foreach( scandir($dir) as $file )
    {
      if( in_array( $file, ['.', '..']) || in_array( $file[0], ['_']) || ( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$dir/$file")))
        continue;

      $name  = is_dir("$dir/$file")  ?  $file  :  pathinfo($file, PATHINFO_FILENAME);
      $suppl = is_file("$dir/$file")
             ? Yaml::parse( file_get_contents("$dir/$file"))
             : Yaml::parse( file_get_contents("$dir/$file/-this.yml"));

      $suppl['category'] = 'S';

      $this->combinedModel->set( $name, $suppl );
    }
  }
}

?>
