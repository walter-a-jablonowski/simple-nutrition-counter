<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'models/functions.php';

/*@

CombinedModel (food model)

combined food model (merged foods and food defaults) also /supplements

- expands foods variants and merges food defaults
- sets some values manuelly cause more precise (salt, fibre)
- used as data source for LayoutView and as food model in app

*/
trait CombinedModel  /*@*/
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

      // Expand food variants into separate food entries first

      $expandedFoods = expand_food_variants( $name, $food );
      
      foreach( $expandedFoods as $foodName => $foodData )
      {
        $foodData['category'] = 'F';
        
        // Merge nutrients from food file (prio) over default foods for each expanded food
        // TASK: maybe we want to add at least an empty key if a type of nutrients is missing

        if( isset( $foodData['type']) && file_exists("data/food_defaults/$foodData[type].yml"))
        {
          $nutrients = Yaml::parse( file_get_contents("data/food_defaults/$foodData[type].yml"));

          foreach( self::NUTRIENT_GROUPS as $groupName )
          {
            if( isset( $nutrients[$groupName] ))
              $foodData[$groupName] = array_merge( $nutrients[$groupName], $foodData[$groupName] ?? []);
            // else
            //   // $foodData[$groupName] = $nutrients[$groupName];
            //   $foodData[$groupName] = $foodData[$groupName] ?? [];
          }

          // Duplicate salt and fibre (food value more precise, overrides food default)

          if( isset($foodData['nutritionalValues']['fibre']))
            $foodData['carbs']['Fibre'] = $foodData['nutritionalValues']['fibre'];

          $foodData['minerals']['Salt'] = $foodData['nutritionalValues']['salt'];
        }

        $this->combinedModel->set( $foodName, $foodData );
      }
    }

    // Supplements

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
