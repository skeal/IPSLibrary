<?
	/*
	 * This file is part of the IPSLibrary.
	 *
	 * The IPSLibrary is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published
	 * by the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * The IPSLibrary is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with the IPSLibrary. If not, see http://www.gnu.org/licenses/gpl.txt.
	 */    

	/**@addtogroup ipsshadowing
	 * @{
	 *
	 * @file          IPSShadowing_ProfileTemp.class.php
	 *
	 * Temperatur Profil Verwaltung
	 */

   /**
    * @class IPSShadowing_ProfileTemp
    *
    * Definiert ein IPSShadowing_ProfileTemp Objekt
    *
    * @author Andreas Brauneis
    * @version
    *   Version 2.50.1, 01.04.2012<br/>
    */
	class IPSShadowing_ProfileTemp {

		/**
		 * @private
		 * ID des Zeit Profiles
		 */
		private $instanceId;
		
		/**
		 * @private
		 * Aktivierung bei Sonnenstand und Helligkeit
		 */
		private $activationByTemp;

		private $brightnessLevel;
		private $brightnessValue;
		private $tempOutdoor;
		private $tempIndoor;
		private $tempDiffShadowing;
		private $tempDiffClosing;
		private $tempDiffOpening;

		/**
		 * @public
		 *
		 * Initialisierung des IPSShadowing_ProfileTime Objektes
		 *
		 * @param integer $instanceId InstanceId Profiles
		 */
		public function __construct($instanceId) {
			$this->instanceId = IPSUtil_ObjectIDByPath($instanceId);
			$this->Init();
		}

		/**
		 * @private
		 *
		 * Initialisierung der internen Variablen
		 *
		 */
		private function Init() {
			$this->brightnessLevel   = GetValue(IPS_GetObjectIDByIdent(c_Control_Brightness, $this->instanceId));
			$this->tempDiffShadowing = GetValue(IPS_GetObjectIDByIdent(c_Control_TempDiffShadowing, $this->instanceId));
			$this->tempDiffClosing   = GetValue(IPS_GetObjectIDByIdent(c_Control_TempDiffClosing, $this->instanceId));
			$this->tempDiffOpening   = GetValue(IPS_GetObjectIDByIdent(c_Control_TempDiffOpening, $this->instanceId));
			$this->tempIndoor        = null;
			$this->tempOutdoor       = null;
			$this->brightnessValue   = null;
			$this->activationByTemp  = true;
			if (IPSSHADOWING_TEMPSENSORINDOOR <> '') {
				$this->tempIndoor = round(GetValue(IPSUtil_ObjectIDByPath(IPSSHADOWING_TEMPSENSORINDOOR)),1);
			}
			if (IPSSHADOWING_TEMPSENSOROUTDOOR <> '') {
				$this->tempOutdoor = round(GetValue(IPSUtil_ObjectIDByPath(IPSSHADOWING_TEMPSENSOROUTDOOR)),1);
			}
			if (IPSSHADOWING_BRIGHTNESSSENSOR <> '') {
				$this->brightnessValue  = round(GetValue(IPSUtil_ObjectIDByPath(IPSSHADOWING_BRIGHTNESSSENSOR)),1);
				$this->activationByTemp = ($this->activationByTemp and $this->brightnessValue>=$this->brightnessLevel);
			}
		}
		
		private function GetActivationByTemp($tempIndoorPath, $tempDiff, $reverse=false) {
			//Example:
			// Aussen >= Innen+Diff
			// Outdoor=16,Indoor=22,  -->  -6 >= 2 -> false
			// Outdoor=20,Indoor=22,  -->  -2 >= 2 -> false
			// Outdoor=26,Indoor=22,  -->   4 >= 2 -> true
			$activationByTemp = $this->activationByTemp;
			$tempIndoor       = $this->tempIndoor;
			if ($tempDiff<>c_TempDiff_NoAction) {
				if ($tempIndoorPath <> '') {
					$tempIndoor = round(GetValue(IPSUtil_ObjectIDByPath($tempIndoorPath)),1);
				}
				if ((IPSSHADOWING_TEMPSENSORINDOOR<>'' or $tempIndoorPath<>'') and IPSSHADOWING_TEMPSENSOROUTDOOR <> '') {
					if ($reverse) {
						$activationByTemp = ($activationByTemp and (($tempIndoor-$this->tempOutdoor) >= $tempDiff));
					} else {
						$activationByTemp = ($activationByTemp and (($this->tempOutdoor-$tempIndoor) >= $tempDiff));
					}
				}
			}
			return $activationByTemp;
		}
		
		public function UpdateProfileInfo() {
			$tempIndoor       = (IPSSHADOWING_TEMPSENSORINDOOR<>'' ? $this->tempIndoor.'�C'  :'"nicht vorhanden"');
			$tempOutdoor      = (IPSSHADOWING_TEMPSENSOROUTDOOR<>''? $this->tempOutdoor.'�C' :'"nicht vorhanden"');
			$brightness       = (IPSSHADOWING_BRIGHTNESSSENSOR<>'' ? $this->brightnessValue.' Lux':'"nicht vorhanden"');
			$activationByTemp = $this->GetActivationByTemp('', $this->tempDiffShadowing);
			$info             = ''.($activationByTemp?'Profil aktiv':'Profil inaktiv').' (Innen='.$tempIndoor.', Aussen='.$tempOutdoor.', Helligkeit='.$brightness.')';
			if (GetValue(IPS_GetObjectIDByIdent(c_Control_ProfileInfo, $this->instanceId)) <> $info) {
				SetValue(IPS_GetObjectIDByIdent(c_Control_ProfileInfo, $this->instanceId), $info);
				IPS_SetVariableProfileAssociation('IPSShadowing_ProfileTemp', 
												  $this->instanceId,  
												  GetValue(IPS_GetObjectIDByIdent(c_Control_ProfileName, $this->instanceId)), 
												  '', 
												  ($activationByTemp?c_Color_ProfileActive:-1));
			}
		}

		public function GetProfileInfo($tempIndoorPath) {
			$info = '';
			if ($tempIndoorPath <> '') {
				$info .= ' Innen='.round(GetValue(IPSUtil_ObjectIDByPath($tempIndoorPath)),1).'�C';
			} elseif (IPSSHADOWING_TEMPSENSORINDOOR <> '') {
				$info .= ' Innen='.$this->tempIndoor.'�C';
			}
			if (IPSSHADOWING_TEMPSENSOROUTDOOR <> '') {
				if ($info<>'') { $info.=', ';}
				$info .= ' Aussen='.$this->tempOutdoor.'�C';
			}
			return $info;
		}
		
		public function ShadowingByTemp($tempIndoorPath) {
			if ($this->tempDiffShadowing==c_TempDiff_NoAction) {
				return $this->GetActivationByTemp($tempIndoorPath, $this->tempDiffClosing);
			} else {
				return $this->GetActivationByTemp($tempIndoorPath, $this->tempDiffShadowing);
			}
		}

		public function CloseByTemp($tempIndoorPath) {
			return $this->GetActivationByTemp($tempIndoorPath, $this->tempDiffClosing);
		}

		public function OpenByTemp($tempIndoorPath) {
			return $this->GetActivationByTemp($tempIndoorPath, $this->tempDiffOpening, true);
		}

		/**
		 * @public
		 *
		 * Neues Profile generieren
		 *
		 * @param string $profileName Name des Profiles
		 * @param integer $tempDiffShadowing Temperatur Differenz f�r Beschattung
		 * @param integer $tempDiffClose Temperatur Differenz f�r Abdunkelung
		 * @param integer $tempDiffOpening Temperatur Differenz f�r �ffnen
		 * @param integer $brightness Helligkeit
		 */
		public static function Create($profileName, $tempDiffShadowing=0, $tempDiffClose=1, $tempDiffOpening=1, $brightness=0) {
			IPSUtils_Include ('IPSInstaller.inc.php', 'IPSLibrary::install::IPSInstaller');
			
			$ScriptIdChangeSettings  = IPSUtil_ObjectIDByPath('Program.IPSLibrary.app.modules.IPSShadowing.IPSShadowing_ChangeSettings');
			$categoryIdprofiles      = IPSUtil_ObjectIDByPath('Program.IPSLibrary.data.modules.IPSShadowing.Profiles.Temp');
			$profileIdx              = count(IPS_GetChildrenIds($categoryIdprofiles)) + 10;
			$profileId               = CreateCategory ($profileName, $categoryIdprofiles, $profileIdx);
			IPS_SetIdent($profileId, (string)$profileId);
			CreateVariable(c_Control_ProfileName,       3 /*String*/,   $profileId, 0,  '~String',                        $ScriptIdChangeSettings, $profileName,       'Title');
			CreateVariable(c_Control_TempDiffShadowing, 1 /*Integer*/,  $profileId, 10, 'IPSShadowing_TempDiffShadowing', $ScriptIdChangeSettings, $tempDiffShadowing, 'Temperature');
			CreateVariable(c_Control_TempDiffClosing,   1 /*Integer*/,  $profileId, 20, 'IPSShadowing_TempDiffClosing',   $ScriptIdChangeSettings, $tempDiffClose,     'Temperature');
			CreateVariable(c_Control_TempDiffOpening,   1 /*Integer*/,  $profileId, 20, 'IPSShadowing_TempDiffOpening',   $ScriptIdChangeSettings, $tempDiffOpening,   'Temperature');
			CreateVariable(c_Control_Brightness,        1 /*Integer*/,  $profileId, 30, 'IPSShadowing_Brightness',        $ScriptIdChangeSettings, $brightness,        'Sun');
			CreateVariable(c_Control_ProfileInfo,       3 /*String*/,   $profileId, 40, '~String',                        null,                    '',                 'Information');

			IPS_SetVariableProfileAssociation('IPSShadowing_ProfileTemp', $profileId, $profileName, "", -1);
			
			return $profileId;
		}

		/**
		 * @public
		 *
		 * Visualisierung des Profiles in einer �bergebenen Kategorie
		 *
		 * @param integer $categoryId ID der Kategory in der die Visualisierungs Links abgelegt werden sollen
		 */
		public function Display($categoryId) {
			IPSUtils_Include ('IPSInstaller.inc.php', 'IPSLibrary::install::IPSInstaller');
			CreateLink('Profil Name',  IPS_GetObjectIDByIdent(c_Control_ProfileName, $this->instanceId), $categoryId, 10);
			$instanceId = CreateDummyInstance("Temperatur Grenzen", $categoryId, 20);
			CreateLink('Differenz Beschattung',  IPS_GetObjectIDByIdent(c_Control_TempDiffShadowing,   $this->instanceId), $instanceId, 10);
			CreateLink('Differenz Abdunkelung',  IPS_GetObjectIDByIdent(c_Control_TempDiffClosing,   $this->instanceId), $instanceId, 20);
			CreateLink('Differenz �ffnen',       IPS_GetObjectIDByIdent(c_Control_TempDiffOpening,   $this->instanceId), $instanceId, 30);
			$id = @IPS_GetObjectIdByName("Hellingkeits Grenze", $categoryId);
			if ($id!==false) {
				EmptyCategory($id);
				IPS_DeleteInstance($id);
			}
			$instanceId = CreateDummyInstance("Helligkeits Grenze", $categoryId, 30);
			CreateLink('Helligkeit',   IPS_GetObjectIDByIdent(c_Control_Brightness, $this->instanceId), $instanceId, 10);
			CreateLink('Profil Info',  IPS_GetObjectIDByIdent(c_Control_ProfileInfo, $this->instanceId), $categoryId, 40);
		}

		/**
		 * @public
		 *
		 * Profile l�schen
		 *
		 */
		public function Delete() {
			IPSUtils_Include ('IPSInstaller.inc.php', 'IPSLibrary::install::IPSInstaller');
			IPS_SetVariableProfileAssociation('IPSShadowing_ProfileTemp', $this->instanceId, '', '', -1);
			DeleteCategory($this->instanceId);
		}

		/**
		 * @public
		 *
		 * Profile umbenennen
		 *
		 * @param string $newName Neuer Name des Profiles
		 */
		public function Rename($newName) {
			IPSUtils_Include ('IPSInstaller.inc.php', 'IPSLibrary::install::IPSInstaller');
			IPS_SetVariableProfileAssociation('IPSShadowing_ProfileTemp', $this->instanceId, $newName, '', -1);
			IPS_SetName($this->instanceId, $newName);
			SetValue(IPS_GetObjectIDByIdent(c_Control_ProfileName, $this->instanceId), $newName);
		}
		
		/**
		 * @public
		 *
		 * Profil ver�ndern
		 *
		 * @param integer $controlId ID der Variable die ver�ndert werden soll
		 * @param integer $value neuer Wert
		 */
		public function SetValue($controlId, $value) {
			SetValue($controlId, $value);
			IPSShadowing_LogChange($this->instanceId, $value, $controlId);
			$this->Init();
			$this->UpdateProfileInfo();
		}

	}

	/** @}*/

?>