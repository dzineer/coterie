<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;

namespace App\DAL;

class DBHelper
{
    private $conn;

    public function __construct($configs)
    {
        $this->conn = DriverManager::getConnection($params, $config);
    }


	//Protects against injection
	public function prepare_query() {

		$arg_list = func_get_args();
		$query = $arg_list[0];
		
		$stmt = $conn->prepare($query);
		return $stmt->fetchAll(array_slice($arg_list,1));
	}

	//Runs a simple parameter-less query
	public function query($query) {
		$result = array();
		foreach ($conn->query($query) as $row) {
			$result[] = $row;
		}
		return $result;
	}

	//Protects against injection. Returns first row only.
	public function prepare_query1() {
		$result = prepare_query($arg_list);
		if ($result) return $result[0];
		else return array();
	}

	//Runs a simple parameter-less query. Returns first row only.
	public function query1($query) {
		$result = query($query);
		if ($result) return $result[0];
		else return array();
	}

}

<?php
//************************************************************************************

//replaces tags with HTML entities.
function clean($string) {
	return htmlentities($string,ENT_QUOTES);
}

function clean_textarea($string) {
		return str_replace("</textarea>","",$string);
	}

//Allows <br>, <b>, <i>, and table tags
function add_html($string) {
	$allow = array("b","i","u","table","tr","th","td");
	
	foreach ($allow as $tag) {
		$string = str_replace("[$tag]","<$tag>",$string);
		$string = str_replace("[/$tag]","</$tag>",$string);
	}
	
	$string = str_replace("[br]","<br />",$string);
	$string = str_replace("[gap]","&nbsp;",$string);
	$string = str_replace("[gap5]","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$string);
	
	//parse a quick table
	// syntax:
	// indicate start of table with [tab]
	// and end of table with [/tab]
	// between tags, a single "|" means "</td><td>"
	// and double "||" means "</td></tr><tr><td>"
	$regex1='/\[tab\]([^]]+)\[\/tab\]/';
	$regex2='/(\[tab\][^]]+\[\/tab\])/';
	
	preg_match_all($regex1, $string, $arr);
	$eResult=array();
	foreach($arr[1] as $k=>$v) {
		$v = str_replace("||", "</td></tr><tr><td>", $v);
		$v = str_replace("|", "</td><td>", $v);
		$eResult[$k]="<table class='borders'><tr><td>".$v."</td></tr></table>";
	}
	foreach($eResult as $r) {
	  $string = preg_replace($regex2, $r, $string, 1);
	}
	
	return $string;
}

//cleans twice, since xml uses escape sequences. Second clean changes added & to &amp;
function clean_for_xml($string) {
	$string = clean(clean($string));
	return $string;
}

//Allows <br>, <b>, <p> and <i>
function add_xml($string) {
	$allow = array("b","i","p","u","table","tr","th","td");
	
	foreach ($allow as $tag) {
		$string = str_replace("[$tag]","&lt;$tag&gt;",$string);
		$string = str_replace("[/$tag]","&lt;/$tag&gt;",$string);
	}
	
	//parse a quick table
	// syntax:
	// indicate start of table with [tab]
	// and end of table with [/tab]
	// between tags, a single "|" means "</td><td>"
	// and double "||" means "</td></tr><tr><td>"
	
	$string = str_replace("[br]","&lt;br /&gt;",$string);
	$string = str_replace("[gap]","&nbsp;",$string);
	$string = str_replace("[gap5]","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$string);
	
	return $string;
}
//************************************************************************************


//changes the page location
function go_to($address) {
	$next_page = "Location: $address";
    header($next_page);
}

//returns to index without a proper login
function require_login() {
	$logged_in = isset($_SESSION['player_id']);
	if (!$logged_in) go_to(root.home);
}

function require_player() {
	require_login();
	if (!player()) go_to(root.home);
}

function require_rules() {
	require_login();
	if (!rules()) go_to(root.home);
}

function require_narr() {
	require_login();
	if (!narrator()) go_to(root.home);
}

function require_st() {
	require_login();
	if (!st()) go_to(root.home);
}

function require_hst() {
	require_login();
	if (!hst()) go_to(root.home);
}

//************************************************************************************

function position($n) {
	$position = $_SESSION['position'];
	return ($position & $n) != 0;
}

function player() {
	return position(1);
}

function rules() {
	return position(2);
}

function narrator() {
	return position(4);
}

function st() {
	return position(8);
}

function hst() {
	return position(16);
}

function get_current_char_name() {
	return get_char_name(get_current_char_id());
}

function get_current_char_id() {
	return $_SESSION['current_char_id'];
}

function set_current_char_id($id) {
	$_SESSION['current_char_id'] = $id;
}

function get_player_name($player_id) {
	$player_name = prepare_query1("SELECT name FROM players WHERE id = ?",$player_id);
	return $player_name['name'];
}

function get_player_id() {
	return$_SESSION['player_id'];
}

//Formats a datestring
function get_date($date) {
	if ($date != NULL) {
		$dh = date_create($date);
		return date_format($dh,"F d, Y");
	} else {
		return "";
	}
}

function get_cycle($dates = 0) {
	$next = query1("SELECT * FROM  `cycles` WHERE  `date` > CURRENT_TIMESTAMP ORDER BY  `date` ASC LIMIT 1");
	if ($dates > 0) return $next;
	return $next['cycle'];
}

function get_prev_cycle($dates = 0) {
	$prev = query1("SELECT * FROM  `cycles` WHERE  `date` < CURRENT_TIMESTAMP ORDER BY  `date` DESC LIMIT 1");
	if ($dates) return $prev;
	return $prev['cycle'];
}

function get_prev2_cycle($dates = 0) {
	$prev = query("SELECT * FROM  `cycles` WHERE  `date` < CURRENT_TIMESTAMP ORDER BY  `date` DESC LIMIT 2");
	if ($dates) return $prev;
	return $prev[1]['cycle'];
}

function get_past_cycles($dates = 0) {
	$prev = query("SELECT * FROM  `cycles` WHERE  `date` < CURRENT_TIMESTAMP ORDER BY  `date` DESC");
	if ($dates) return $prev;
	
	$past = array();
	foreach ($prev as $cycle) {
		$past[] = $cycle['cycle'];
	}
	return $past;
}

function is_locked($lock) {
	$locked = prepare_query1("SELECT * FROM locks WHERE name = ?",$lock);
	return $locked['locked'];
}
//************************************************************************************
//General lookups
function get_char_name($character_id) {
	if ($character_id== NULL) return "N/A";
	$char_name = prepare_query1("SELECT name FROM characters WHERE id = ?",$character_id);
	return $char_name['name'];
}

function get_characters($player_id) {
	if (!narrator()) {
		$result = prepare_query("SELECT id, name FROM characters WHERE player = ? ORDER BY characters.status ASC, name ASC",$player_id);
	} else {
		$result = query("SELECT characters.id AS id, characters.name AS name
							FROM characters
							INNER JOIN character_type 
								ON characters.status = character_type.id
							ORDER BY character_type.category ASC, characters.name ASC");
	}
	return $result;
}

function get_character_options($select) {
	$player = get_player_id();
	
	if (!isset($select)) $select = get_current_char_id();
	
	$allowed_char = get_characters($player);
	
	if ($allowed_char) {
		foreach($allowed_char as $row) {
			$id = $row['id'];
			$name = $row['name'];
			echo "<option value='$id'";
	
			if ($id == $select) echo "selected='selected'";
	
			echo ">$name</option>";
		}
	}
}

function get_all_players() {
	$query = "SELECT * FROM players WHERE position&1 ORDER BY name ASC";
	return query($query);
}

function get_all_sts() {
	$query = "SELECT * FROM players WHERE position&8";
	return query($query);
}

function get_all_statii() {
	$query = "SELECT * FROM character_type ORDER BY category ASC";
	return query($query);
}

function get_creature_types() {
	$query = "SELECT * FROM creature_type ORDER BY type ASC";
	return query($query);
}

function get_all_species() {
	$query = "SELECT * FROM species ORDER BY name ASC";
	return query($query);
}

function get_all_natures() {
	$query = "SELECT * FROM nature ORDER BY nature ASC";
	return query($query);
}

function get_all_paths() {
	$query = "SELECT * FROM paths";
	return query($query);
}
//************************************************************************************

//calculates the pools based on merit, flaws, abilites, and attributes.
//does not include: path, disciplines, species & creature modifiers
function get_pool($char_id,$pool) {
	$query = "SELECT IF( down = 1, TRUNCATE(SUM(p),0), CEILING(ABS(SUM(p)))*SIGN(SUM(p)) ) AS pool
				FROM (
				SELECT 
					IF(char_abilities.ability_id IS NULL,pools.`default`,char_abilities.dots)*pools.multiplier AS p,
					pools.round_down AS down
				FROM pools 
				LEFT JOIN  
				(	SELECT * 
					FROM charsheet_abilities
					WHERE charsheet_abilities.character_id = ?
				) AS char_abilities 
					ON pools.ability = char_abilities.ability_id
				WHERE pools.pool = '$pool' 
					AND pools.merit IS NULL

				UNION ALL

				SELECT charsheet_merits.dots*pools.multiplier AS p, pools.round_down AS down
				FROM pools INNER JOIN charsheet_merits
					ON pools.merit = charsheet_merits.merit_id
				WHERE pools.pool = '$pool'
					AND charsheet_merits.character_id = ?
				) AS t1";
	$result = prepare_query1($query,$char_id,$char_id);
	return $result['pool'];
}

//Humanities are calculated in separate file
include root."get_humanity.php";

function get_clan($char_id) {
	$results = get_organizations($char_id,"Clan");
	$clan_array = array();
	foreach ($results as $clan) {
		$clan_array[] = $clan['name'];
	}
	return implode(", ",$clan_array);
}

function get_sect($char_id) {
	$results = get_organizations($char_id,"Sect");
	$sect_array = array();
	foreach ($results as $sect) {
		$sect_array[] = $sect['name'];
	}
	return implode(", ",$sect_array);
}

function get_other_orgs($char_id) {
	$results = get_organizations($char_id,"Other");
	$other_array = array();
	foreach ($results as $other) {
		$other_array[] = $other['name'];
	}
	return implode(", ",$other_array);
}

//$orgs can be Sect, Clan, or Other
function get_organizations($char_id,$orgs = NULL) {
	$query = "SELECT organizations.organization AS name, organizations.type AS type, organizations.id AS id, charsheet_organizations.id AS entry_id
				FROM charsheet_organizations
				INNER JOIN organizations
					ON charsheet_organizations.organization_id = organizations.id
				WHERE charsheet_organizations.character_id = ?";
				
	if (($orgs=="Sect")||($orgs=="Clan")||($orgs=="Other")) {
		$query.=" AND organizations.type = '$orgs'";
	}
	
	$query.= " ORDER BY organizations.type ASC, organizations.organization ASC";
	return prepare_query($query,$char_id);
}


//************************************************************************************
//Boons
function get_blood_bonds($char_id) {
	$query = "SELECT b.id AS entry_id, c.name AS char_name, b.number AS number, b.obligation AS type
				FROM charsheet_obligations AS b
				INNER JOIN characters AS c
					ON b.owed_to = c.id
				WHERE b.owed_by = ? AND b.obligation = 'Blood Bond' ORDER BY b.number DESC";
	return prepare_query($query,$char_id);
}

function get_boons_owed_by($char_id) {
	$query = "SELECT b.id AS entry_id, c.name AS char_name, b.obligation AS boon, b.number AS number
				FROM charsheet_obligations AS b
				INNER JOIN characters AS c
					ON b.owed_to = c.id
				WHERE b.owed_by = ? AND b.obligation LIKE '%Boon' OR b.obligation = 'Unspecified Favor'
				ORDER BY b.obligation ASC, c.name ASC";
	return prepare_query($query,$char_id);
}

function get_boons_owed_to($char_id) {
	$query = "SELECT b.id AS entry_id, c.name AS char_name, b.obligation AS boon, b.number AS number
				FROM charsheet_obligations AS b
				INNER JOIN characters AS c
					ON b.owed_by = c.id
				WHERE b.owed_to = ? AND b.obligation LIKE '%Boon' OR b.obligation = 'Unspecified Favor'
				ORDER BY b.obligation ASC, c.name ASC";
	return prepare_query($query,$char_id);
}

function get_vinculum($char_id) {
	$query = "SELECT b.id AS entry_id, o.organization AS char_name, b.obligation AS type
				FROM charsheet_obligations AS b
				INNER JOIN organizations AS o
					ON b.pack = o.id
				WHERE b.owed_by = ? AND b.obligation = 'Vinculum'";
	return prepare_query($query,$char_id);
}


//************************************************************************************
function get_references($char_id,$reference) {
	$entry = prepare_query1("SELECT * FROM reference WHERE name LIKE ?",$reference);
	
	$matcher = array("abilities"=>"ability", "disciplines"=>"discipline", "merits"=>"merit");
	$table = $entry['type'];
	$column = $matcher[$table]."_id";
	$table_id = $entry['id'];
	
	$results = prepare_query("SELECT $table.*, charsheet_$table.dots AS dots, charsheet_$table.dots_temp AS dots_temp
					FROM $table INNER JOIN charsheet_$table ON charsheet_$table.$column = $table.id
					WHERE charsheet_$table.character_id = ? AND $table.id = ?",$char_id,$table_id);
	return $results;
}

function get_reference($char_id,$reference) {
	$arr = get_references($char_id,$reference);
	return $arr[0];
}

//Abilities and Merits
function get_attributes($char_id) {
	$query = "SELECT abilities.ability AS name,
					charsheet_abilities.*,
					charsheet_abilities.id AS entry_id
				FROM charsheet_abilities
				INNER JOIN abilities
					ON charsheet_abilities.ability_id = abilities.id
				WHERE charsheet_abilities.character_id = ? AND abilities.type = 'Attribute'
				ORDER BY abilities.id ASC";
	return prepare_query($query,$char_id);
}

function get_all_abilities($char_id) {
	$query = "SELECT abilities.ability AS name,
					abilities.*,
					charsheet_abilities.*,
					charsheet_abilities.id AS entry_id
				FROM charsheet_abilities
				INNER JOIN abilities
					ON charsheet_abilities.ability_id = abilities.id
				WHERE charsheet_abilities.character_id = ? AND NOT abilities.type = 'Attribute'
				ORDER BY type ASC, name ASC, note ASC";
	return prepare_query($query,$char_id);
}

//Matches on LIKE
function get_ability_by_name($char_id,$ability) {
	$query = "SELECT charsheet_abilities.dots AS dots, charsheet_abilities.dots_temp AS dots_temp, abilities.*
				FROM charsheet_abilities
				INNER JOIN abilities
					ON charsheet_abilities.ability_id = abilities.id
				WHERE charsheet_abilities.character_id = ? AND abilities.ability LIKE ?";
	$results = prepare_query($query,$char_id,$ability);
	return $results;
}


function get_all_merits($char_id) {
	$query = "SELECT
					charsheet_merits.*,
					charsheet_merits.id AS entry_id, 
					merits.merit AS name, 
					merits.mechanics_short AS mech,
					merits.merit_or_flaw AS merit_or_flaw
				FROM charsheet_merits
				INNER JOIN merits
					ON charsheet_merits.merit_id = merits.id
				WHERE character_id = ?
				ORDER BY merits.merit_or_flaw ASC, merits.merit ASC";
	return prepare_query($query,$char_id);
}

function get_merit_by_name($char_id,$merit) {
	$query = "SELECT charsheet_merits.dots AS dots, charsheet_merits.dots_temp AS dots_temp, merits.*
				FROM charsheet_merits
				INNER JOIN merits
					ON charsheet_merits.merit_id = merits.id
				WHERE charsheet_merits.character_id = ? AND merits.merit LIKE ?";
	$results = prepare_query($query,$char_id,$merit);
	return $results;
}

//************************************************************************************
//Discipline Functions
function get_disciplines($char_id) {
	$query = "SELECT charsheet_disciplines.*,
					charsheet_disciplines.id AS entry_id,
					disciplines.*
				FROM charsheet_disciplines
				INNER JOIN disciplines
					ON charsheet_disciplines.discipline_id = disciplines.id
				WHERE charsheet_disciplines.character_id = ?
				ORDER BY disciplines.name ASC";
	return prepare_query($query,$char_id);
}

//matches on LIKE
function get_discipline_by_name($char_id,$discipline) {
	$query = "SELECT charsheet_disciplines.dots AS dots, disciplines.*
				FROM charsheet_disciplines
				INNER JOIN disciplines
					ON charsheet_disciplines.discipline_id = disciplines.id
				WHERE charsheet_disciplines.character_id = ? AND disciplines.name LIKE ?";
	return prepare_query1($query,$char_id,$discipline);
}

//Exceptions: Presense 1, Auspex 5
//$discipline_dots should be the MAXIMUM level of the Discipline the character has
function get_discipline_pools($char_id,$discipline_id,$discipline_dots) {
	$results = array();
	
	//Presense 1: Awe
	if ($discipline_id == 11) {
		$awe = prepare_query("SELECT SUM(IF(char_abilities.ability_id IS NULL,pools.`default`,char_abilities.dots)) AS pool
				FROM pools 
				LEFT JOIN  
				(	SELECT * 
					FROM charsheet_abilities
					WHERE charsheet_abilities.character_id = ?
				) AS char_abilities 
				ON pools.ability = char_abilities.ability_id
				WHERE pools.pool = 'awe1' OR pools.pool = 'awe2' OR pools.pool = 'awe3'
				GROUP BY pools.pool",$char_id);
		$max = -10;
		foreach ($awe as $pool) {
			if ((int)$pool['pool'] > $max) $max = (int)$pool['pool'];
		}
		$results[] = array("level"=>1,"pool_name"=>"Dice pool: ","pool"=>($discipline_dots+$max));
	}
	
	
	$query = "SELECT n.level, CONCAT('Dice Pool',IFNULL(n.name,''),': ') AS pool_name,
				SUM(IF(a.id IS NULL, p.default*(IF(p.ability_id IS NULL, ?, 1)), a.dots)) + ? AS pool
				FROM disciplines_pool_names AS n
				INNER JOIN disciplines_pools AS p
					ON n.id = p.pool_id
				LEFT JOIN (
					SELECT * FROM charsheet_abilities WHERE character_id = ?
				) AS a
				ON p.ability_id = a.ability_id
				WHERE n.discipline = ? AND n.level <= ?
				GROUP BY p.pool_id";
	$results = array_merge($results,prepare_query($query,$discipline_dots,$discipline_dots,$char_id,$discipline_id,$discipline_dots));
	
	//Auspex 5: Astral Speed
	if (($discipline_id == 2)&&($discipline_dots>=5)) {
		$astral = get_pool($char_id,'astral_speed');
		$results[] = array("level"=>5,"pool_name"=>"Dice pool- Astral Speed: ","pool"=>$astral);
	}
	
	return $results;
}

function get_discipline_resists($char_id,$extras) {
	//get centering
	$centering = get_reference($char_id,"Centering");
	$centering = (int)$centering['dots'];
	$no_pool = "--";
	$resist_array = array();
	
	$default_list = array("Animalism","Auspex","Dementation","Dominate","Obfuscate","Presence","Thaumaturgy");
	$extra_list = array("Chimerstry","Obteneration","Quietus","Serpentis","Thanatosis","Valeren","Vicissitude");
	
	$list = $default_list;
	//prepare query
	global $dbh;
	$query = "SELECT p AS name, SUM(n)+$centering AS pool
				FROM (
				SELECT 
					IF(char_abilities.ability_id IS NULL,pools.`default`,char_abilities.dots)*pools.multiplier AS n,
					pools.pool AS p
				FROM pools 
				LEFT JOIN  
				(	SELECT * 
					FROM charsheet_abilities
					WHERE charsheet_abilities.character_id = ?
				) AS char_abilities 
					ON pools.ability = char_abilities.ability_id
				WHERE pools.pool LIKE ? 
					AND pools.merit IS NULL

				UNION ALL

				SELECT charsheet_merits.dots*pools.multiplier AS n,
					pools.pool AS p
				FROM pools INNER JOIN charsheet_merits
					ON pools.merit = charsheet_merits.merit_id
				WHERE pools.pool LIKE ?
					AND charsheet_merits.character_id = ?
				) AS t1
				GROUP BY p ORDER BY p ASC";
	
	$stmt = $dbh->prepare($query);
	
	foreach ($list as $disc) {
		$stmt->execute(array($char_id,$disc."%",$disc."%",$char_id));
		$result = $stmt->fetchAll();
		
		$this_pool = array();
		$i = 0;
		for ($p = 1; $p <= 5; $p++) {
			if (substr($result[$i]['name'], -1) == $p) {
				$this_pool[] = (int)$result[$i]['pool'];
				$i++;
			} else {
				$this_pool[] = $no_pool;
			}
		}
		
		$resist_array[] = "\t\t<li><span>$disc:</span>".implode("/",$this_pool)."</li>\n";
	}
	
	
	return $resist_array;
}


//************************************************************************************
//Influence Functions

//dumps information about an influence: charsheet_influences, selected columns from influence_categories and influence_centers
function get_influence($id) {
	return prepare_query1("SELECT cat.id AS category_id, cat.category AS category, 
								c.id AS center_id, c.center AS center, c.XPM AS XPM, c.header as header,
								i.*
							FROM influence_categories AS cat
							INNER JOIN influence_centers AS c
							ON c.category = cat.id
							INNER JOIN charsheet_influences AS i
							ON i.influence = c.id WHERE i.id = ?",$id);
}

function get_influences($char_id) {
	$query = "SELECT cat.id AS category_id, cat.category AS category, 
					c.id AS center_id, c.center AS center, c.XPM AS XPM, c.header as header,
					i.*
				FROM influence_categories AS cat
				INNER JOIN influence_centers AS c
					ON c.category = cat.id
				INNER JOIN charsheet_influences AS i
					ON i.influence = c.id
				WHERE i.character_id = ?
				ORDER BY cat.id ASC, c.center ASC";
	return prepare_query($query,$char_id);
}

//takes the result of get_influence, calculates the level due to XP_total
function influence_level($influence) {
	// level*(level+1)*xpm/2 = xp total
	// n= level; m = xpm; t = xp total
	// n^2 + n - 2t/m = 0
	// n = [-1 +/- sqrt(1-4*(-2t/m))]/2  => must be positive number
	// n = 0.5 * [-1 + sqrt(1 + 8*t/m)]  => then round down
	
	$level = (int)(0.5*(-1 + sqrt(1 + 8*$influence['XP_total']/$influence['XPM'])));
	
	if ($level > 5) {
		//XP needed = 3*XP of previous Level
		//total for level 5 = (n*(n+1)*m/2) = (5*6/2) * xpm = 15 * xpm
		//t = 15 * m * 3^(n - 5)
		//t/(15*m) = 3^(n - 5)
		//n = log3(t/(15*m)) + 5
		
		$level = (int)(log($influence['XP_total']/(15*$influence['XPM']),3) + 5);
	}
	
	if ($level > $influence['dots_original'] + 1) {
		$level = $influence['dots_original'] + 1;
	}
	
	return $level;
}

//returns the character_id of the owner of an influence
function get_influence_owner($id) {
	$influence = prepare_query1("SELECT character_id FROM charsheet_influences WHERE id = ?",$id);
	return $influence['character_id'];
}

function get_maps_by_owner($char_id) {
	return prepare_query("
		SELECT influence_maps.* FROM influence_maps
		INNER JOIN cycles ON influence_maps.cycle = cycles.cycle
		WHERE owner = ? AND ( (influence_maps.type = ? AND influence_maps.strength > influence_maps.opposing_conceal) OR influence_maps.type = ?)
		ORDER BY cycles.date DESC, influence_maps.type DESC",
		$char_id,"Map","Lead");
}

function get_maps_by_influence($id) {
	return prepare_query("
		SELECT influence_maps.*, characters.name
		FROM influence_maps INNER JOIN characters ON influence_maps.owner = characters.id
		RIGHT JOIN (
			SELECT DISTINCT m.owner, cycles.date
			FROM influence_maps AS m
			INNER JOIN cycles
			ON m.cycle = cycles.cycle
			WHERE m.influence = ? AND ( (m.type = ? AND m.strength > m.opposing_conceal) OR m.type = ?)
			GROUP BY m.owner
			ORDER BY MAX(cycles.date) DESC ) AS m
		
		ON m.owner = influence_maps.owner 
		WHERE influence_maps.influence = ?
		ORDER BY m.date DESC, m.owner ASC, influence_maps.type DESC",
	$id,"Map","Lead",$id);
}

function get_maps_by_action($action_id) {
	return prepare_query("SELECT * FROM influence_maps WHERE action = ?",$action_id);
}

function link_action($action,$path = '') {
	return '<a href="'.$path.'submit_action.php?id='.$action.'">Action '.$action.'</a>';
}
?>