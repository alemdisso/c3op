    <?php
/**
 * PHP class for processing Breadcrumbs
 * It will
 * Developed using PHP 5 (http://www.php.net/)
 *
 *
 * Version 1.0 April 14, 2009
 *
 *
 * For assistance please contact:
 * Also if you would like leave me a message if you are using this class
 * Support <njau_ndirangu(at)yahoo.com>
 *
 *
 * Please keep this header information here
 *
 * Description: This class can generate bread crumb like navigation trails
 * It outputs an HTML ordered list of navigation links managed by CSS.
 *
 *
 * Usage:
 * session_start();
 * $nav = new breadcrumb();
 * $nav->url_count=10 change total of links displayed on the page
 * $output=$nav ->add("descriptive name", "/page_url.php");
 * echo $output;
 *
 * Example CSS:
 * #breadcrumb ul li{
 *   list-style-image: none;
 *   display:inline;
 *   padding: 0 3px 0 0;
 *   margin: 3px 0 0 0;
 * }
 *
 * #breadcrumb ul{
 *   margin:0;padding:0;
 *   list-style-type: none;
 *   padding-left: 1em;
 * }
 *
 * This class was adapted to better adjust to the C3op project
 *
 */

class C3op_Util_Breadcrumb{


   public $separator ="|";
   public $reset=false;//reset the $_session variable
   private $crumbs = array();
   private $maxUrlCount=10;// number of links allowed on the page
   private $homepage="#Home";


   /*
    * Constructor
    */
   public function __construct($reset = false, $homepage = "#Home"){
       $this->reset = $reset;
       $this->homepage = $homepage;

      if (!empty($_SESSION['breadcrumb'])){
         $this->crumbs =$this->safe_array_merge($this->crumbs,$_SESSION['breadcrumb']);
      }
      if($this->reset){

      	$this->unset_variable();
      }

   }
   /*
    * reset the $_session variable
    *
    *
    *
    *
    */
	public function reset_variable(){

		if($this->reset){

			$this->unset_variable();
		}
	}
   /*
    * Add a crumb to the trail:
    * @param $label - The string to display
    * @param $uri - The url underlying the label
    *
    *
    */
   public function add($label, $uri){

      $crumb = array('uri' =>$uri, 'label' => $label);

      if (empty($_SESSION['breadcrumb'])){
//        $this->crumbs[] = array('uri' =>"/", 'label' => $this->homepage);
//        $_SESSION['breadcrumb']=$this->crumbs;
          $_SESSION['breadcrumb'] = array();

      }
      $_SESSION['breadcrumb'][] = $crumb;


         //$check_duplicate=$this->check_duplicate($crumb, $_SESSION['breadcrumb']);
//         if($check_duplicate > 0){
//
//         	$break_array=$this->break_array($_SESSION['breadcrumb'],$crumb);
//
//         	$array_merge=$this->safe_array_merge($break_array,$crumb);
//         	//clear session variable
//         	 //and prepare the session variable to be loaded with the exact information location
//         	$this->unset_variable();
//
//         	$output=$this->output($break_array,$label,$uri);
//
//
//         	//put the merged array in the session variable
//         	//this is where the user is at the moment
//         	 $_SESSION['breadcrumb'] =$array_merge;
//
//
//         }else {
//

//         	$output=$this->output($_SESSION['breadcrumb'],$label,$uri);

         	 //$_SESSION['breadcrumb'] = $this->safe_array_merge($_SESSION['breadcrumb'],$crumb);

//         }
//



        //return the output

        return $_SESSION['breadcrumb'];

   }

   public function getCrumbs()
   {
       return $this->crumbs;
   }

   /**
		*This part will check for repeated values in $_SESSION array
		* The idea is to check if the label and link are identical
		* If they are identical that will mean that some one went back on the trail
		* This function will pop out the other trails to show the remaining ones
		*
		*
		* @param
		* @access private
		*
		*/

   private function check_duplicate($array1,$array2){


   	 $array=array_intersect_assoc($array1, $array2);

   	 //return the number of occurences
   	 return count($array);
   }

   /**
     *merges the elements of one or more arrays together so that the values of one are appended to the end of the previous one.
     *If the input arrays have the same string keys, then the later value for that key will overwrite the previous one.
     *
     * @return merge array
     * @access private
     *
     *
     *
     * */
	private  function safe_array_merge($array1,$array2)
	{
		return array_merge($array1, $array2);

	}

	/**
		* This section will check the exact position in the array where the user went back on the trail
		*
		* The rest will be dumped and the new link started from there
		*
		* @param
		* @access private
		*
		*/
	private function break_array($array1,$array2){


		$count=0;
		foreach ($array1 as $key=>$value){
			$count++;
			if((isset($array2[$key])) && ($value == $array2[$key])){

				$num=$count;
			}


		}

		 while((count($array1)+1) > ($num)){

           	array_pop($array1); //prune until we reach the $level we've allocated to this page

         }

		return $array1;
	}

	private function purge_array($array1,$array2){


		$count=0;
		foreach ($array1 as $key=>$crumb){
			$count++;
			if(($crumb['uri'] == ($array2['uri'])) && ($crumb['label'] == ($array2['label']))){
                            unset($array1[$key]);
			}
		}

		return $array1;
	}


	/**
     * Unsets $_SESSION variable

     */
	private  function unset_variable()
	{
			$_SESSION['breadcrumb']=null;
			unset($_SESSION['breadcrumb']);

	}
}
?>
