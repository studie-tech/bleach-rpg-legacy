<?php
/*
 *								Battle calculations class
 *		Abstract class contains each possible variation of the damage calculations
 *		And other calculations used during battle, file is included in battle.inc.php
 *					But is used throughout all battle related classes.
 *		Version: 1.1										Last modified: 27-08-2007
 */

abstract class calc{	
	
	/*
	 *		Calculate PERC damage of health, useless function but just in case
	 *		We want to elaborate on it, it's been standardized here.
	 */
	public function calc_perc_damage($target_data,$percent,$perc_of = 'max'){
		$perc = $target_data[0][$perc_of.'_health'] / 100;
		return $perc * $percent;
	}
	
	/*
	 *		Damage calculation for dual offense attacks
	 *		These include the STCHA attack, as well as all weapons with a DMG modifier other than weap.
	 */
	
	public function calc_double_damage($user_data,$target_data,$type1,$type2,$stat1,$stat2,$power = 0){
		/* 
		 *				Offensive side
		 */
		//	Set balance / desparation.
		$desparation = calc::calculate_desparation($user_data['lifeperc']);
		$balance = calc::calculate_balance($user_data[0]['willpower']);
		//	Set random factor
		$rand = rand($balance,$desparation) / 100;
		//	Set stat factors
		$factor1 = $user_data[0][$stat1] / 20;
		$factor2 = ceil($user_data[0][$stat2] / 40);
		$offense = ($user_data[0][$type1.'_off'] + $user_data[0][$type2.'_off']) / 2;
		//	Calculate power factor
		$power = 1 + sqrt($power / 5);
		//	Calculate damage
		$damage = $rand * (sqrt(($offense * $power) * $factor1) + $factor2);
		$damage = ceil($damage);
		/*
		 *				Defensive side
		 */
		$defense = ($target_data[0][$type1.'_def'] + $target_data[0][$type2.'_def']) / 4;
		$factor1  = $target_data[0][$stat1] / 10;
		//	 Set data:
		$damage = calc::calc_armorbonus(round($damage - $def),$user_data['armor']);
		if($damage < 0){
			$damage = 0;
		}
		return $damage;
	}
	
	/* 
	 *		Calculate and return the entity's balance, used in damage calculations
	 *		Seperate function to simplify modifications.
	 */
	public function calculate_balance($willpower){
		return 50 + (floor(sqrt(($willpower / 2))));
	}
	
	/*
	 *		Calculate and return entity's desparation, used in damage calculations
	 *		Seperate function to simplify modifications.
	 */
	public function calculate_desparation($life_perc){
		return 50 + (floor(-0.6289 * $life_perc + 52.67));
	}

	/*
	 *		Calculates value without deducting a defensive value.
	 *		used for effects that ignore defense, or calculation of healing effects
	 *		Outcome is lower than that of the offensive part of the damage calculation
	 */
	
	public function calc_value($user_data,$type,$stat,$power = 0){
		//	Set balance / desparation
		$desparation = calc::calculate_desparation($user_data['lifeperc']);
		$balance =  calc::calculate_balance($user_data[0]['willpower']);
		//	Calculate random factor:
		$rand = rand($balance,$desparation) / 100;
		//	Calculate / set factors
		$factor1 = $user_data[0][$stat] / 5;
		$offense = $user_data[0][$type.'_off'];
		//	Calculate power factor
		$power = 1 + sqrt($power / 5);
		//	Calculate value
		$value = $rand * (sqrt(($offense * $power) * $factor1));
		if($value < 0){
			$value = 0;
		}
		return $value;
	}
	
	/*
	 *		Damage calculation, regular
	 *		Utilizes both the defensive and offensive factors of a single type.
	 *		Utilized by all CALC damage generating effects
	 */
	
	public function calc_damage($user_data,$target_data,$type,$stat1,$stat2,$power = 0){
		/*
		 *					Offensive part
		 */
		//	Set balance / desparation
		$desparation = calc::calculate_desparation($user_data['lifeperc']);
		$balance =  calc::calculate_balance($user_data[0]['willpower']);
		//	Calculate random factor:
		$rand = rand($balance,$desparation) / 100;
		//	 Set factors
		$factor1 = sqrt($user_data[0][$stat1] / 10);
		$factor2 = $user_data[0][$stat2] / 20;
		$offense = $user_data[0][$type.'_off'];
		//	Calculate power rating
		$power = 1 + sqrt($power / 5);
		//	Calculate damage
		$damage = $rand * (sqrt(($offense * $power) * $factor1) + $factor2);
		$damage = round($damage * 2);
		/*
		 *					Defensive part
		 */
		$defense = $target_data[0][$type.'_def'];
		$factor1 = $target_data[0][$stat1] / 5;
		//	Calculate Defense
		$def = round(sqrt($defense * $factor1));
		//	Set damage
		$damage = calc::calc_armorbonus(round($damage - $def),$user_data['armor']);
		if($damage < 0){
			$damage = 0;
		}
		return $damage;
	}

    /*
     *      Deduct armor damage reduction bonus from the damage
     *      Returns reduced damage, called internally
     */
     
     private function calc_armorbonus($damage,$armor){
         $reduction = (pow(60,-8) * pow($armor,2) + pow(40,-4) * $armor) * 2600;    
         $newdam = $damage - (($damage / 100) * $reduction);
         return round($newdam);
     }
}
?>