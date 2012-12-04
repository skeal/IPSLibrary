<?
	/**@addtogroup ipscomponent
	 * @{
	 *
 	 *
	 * @file          IPSComponentDimmer_Zwave.class.php
	 * @author        Andreas Brauneis (Modifiziert von Thomas Klupp)
	 *
	 *
	 */

   /**
    * @class IPSComponentDimmer_Zwave
    *
    * Definiert ein IPSComponentDimmer_Zwave Object, das ein IPSComponentDimmer Object für Zwave implementiert.
    *
    * @author Andreas Brauneis (Modifiziert von Thomas Klupp)
    * @version
    * Version 2.50.1, 04.12.2012<br/>
    */

	IPSUtils_Include ('IPSComponentDimmer.class.php', 'IPSLibrary::app::core::IPSComponent::IPSComponentDimmer');

	class IPSComponentDimmer_Zwave extends IPSComponentDimmer {

		private $instanceId;
	
		/**
		 * @public
		 *
		 * Initialisierung eines IPSComponentDimmer_Zwave Objektes
		 *
		 * @param integer $instanceId InstanceId des Zwave Devices
		 */
		public function __construct($instanceId) {
			$this->instanceId = IPSUtil_ObjectIDByPath($instanceId);
		}

		/**
		 * @public
		 *
		 * Funktion liefert String IPSComponent Constructor String.
		 * String kann dazu benützt werden, das Object mit der IPSComponent::CreateObjectByParams
		 * wieder neu zu erzeugen.
		 *
		 * @return string Parameter String des IPSComponent Object
		 */
		public function GetComponentParams() {
			return get_class($this).','.$this->instanceId;
		}

		/**
		 * @public
		 *
		 * Function um Events zu behandeln, diese Funktion wird vom IPSMessageHandler aufgerufen, um ein aufgetretenes Event 
		 * an das entsprechende Module zu leiten.
		 *
		 * @param integer $variable ID der auslösenden Variable
		 * @param string $value Wert der Variable
		 * @param IPSModuleDimmer $module Module Object an das das aufgetretene Event weitergeleitet werden soll
		 */
		public function HandleEvent($variable, $value, IPSModuleDimmer $module){
		}

		/**
		 * @public
		 *
		 * Zustand Setzen 
		 *
		 * @param integer $power Geräte Power
		 * @param integer $level Wert für Dimmer Einstellung (Wertebereich 0-100)
		 */
		public function SetState($power, $level) {
			$levelZW = $level;
			if (!$power) {
				ZW_SwitchMode($this->instanceId, false);
			} else {
			
			/**	ZW_DimSet für den zuletzt bekannten Dimmzustand ($Intensity) zu haben
			 *	ZW_SwitchMode dimmt auf 100%
			*/
				ZW_DimSet($this->instanceId, $levelZW);
				// ZW_SwitchMode($this->instanceId, true);
			}
			
		}

		/**
		 * @public
		 *
		 * Liefert aktuellen Power Zustand des Dimmers
		 *
		 * @return boolean Gerätezustand On/Off des Dimmers
		 */
		public function GetPower() {
			return GetValue(IPS_GetVariableIDByName('Boolean', $this->instanceId));
		}

	}

	/** @}*/
?>