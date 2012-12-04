<?
	/**@addtogroup ipscomponent
	 * @{
	 *
 	 *
	 * @file          IPSComponentSwitch_Zwave.class.php
	 * @author        Andreas Brauneis (Modifiziert von Thomas Klupp)
	 *
	 *
	 */

   /**
    * @class IPSComponentSwitch_Zwave
    *
    * Definiert ein IPSComponentSwitch_Zwave Object, das ein IPSComponentSwitch Object für Zwave implementiert.
    *
    * @author Andreas Brauneis (Modifiziert von Thomas Klupp)
    * @version
    * Version 2.50.1, 04.12.2012<br/>
    */

	IPSUtils_Include ('IPSComponentSwitch.class.php', 'IPSLibrary::app::core::IPSComponent::IPSComponentSwitch');

	class IPSComponentSwitch_Zwave extends IPSComponentSwitch {

		private $instanceId;
	
		/**
		 * @public
		 *
		 * Initialisierung eines IPSComponentSwitch_Zwave Objektes
		 *
		 * @param integer $instanceId InstanceId des Zwave Devices
		 */
		public function __construct($instanceId) {
			$this->instanceId = IPSUtil_ObjectIDByPath($instanceId);
		}

		/**
		 * @public
		 *
		 * Function um Events zu behandeln, diese Funktion wird vom IPSMessageHandler aufgerufen, um ein aufgetretenes Event 
		 * an das entsprechende Module zu leiten.
		 *
		 * @param integer $variable ID der auslösenden Variable
		 * @param string $value Wert der Variable
		 * @param IPSModuleSwitch $module Module Object an das das aufgetretene Event weitergeleitet werden soll
		 */
		public function HandleEvent($variable, $value, IPSModuleSwitch $module){
			$module->SyncState($value, $this);
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
		 * Zustand Setzen 
		 *
		 * @param boolean $value Wert für Schalter
		 */
		public function SetState($value) {
			if (!$value) {
				ZW_SwitchMode($this->instanceId, false);
			} else {
				ZW_SwitchMode($this->instanceId, true);
			}
		}

		/**
		 * @public
		 *
		 * Liefert aktuellen Zustand
		 *
		 * @return boolean aktueller Schaltzustand  
		 */
		public function GetState() {
			GetValue(IPS_GetVariableIDByName('Status', $this->instanceId));
		}

	}

	/** @}*/
?>