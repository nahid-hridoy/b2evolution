<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><!-- InstanceBegin template="/Templates/b2evodistrib.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" --> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>b2 evolution: Database tables installation</title>
<!-- InstanceEndEditable --><link href="b2evo.css" rel="stylesheet" type="text/css" />
 
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body>
<div id="rowheader" >
<h1><a href="http://b2evolution.net/" title="b2evolution: Home"><img src="../img/b2evolution_logo.png" alt="b2evolution" width="472" height="102" border="0" /></a></h1>
<div id="tagline">A blog tool like it oughta be!</div>
<h1 id="version">Version: 0.8.7</h1>
<div id="quicklinks">Setup Links: <a href="../../index.html">My b2evo</a> &middot; <a href="http://b2evolution.net/man/">Online Manual</a> &middot; <a href="install.php">My DB Install</a> &middot; <a href="../index.php">My Blogs</a> &middot; <a href="../admin/b2edit.php">My Back-Office</a></div>
</div>
<!-- InstanceBeginEditable name="Main" -->
<h2>Database tables installation</h2>
<p>PHP version: <?php echo phpversion(); ?></p>
<?php
	list( $version_main, $version_minor ) = explode( '.', phpversion() );
	if( ($version_main*100+$version_minor) < 401 )
	{
		die( '<strong>The minimum requirement for this version of b2evolution is PHP Version 4.1.0!</strong>');
	}

	require_once (dirname(__FILE__).'/../conf/_config.php'); 
 
 ?>

<p>These are your settings from the config file: (If you don't see correct settings here, STOP before going any further, and check your configuration.)</p>
<pre>
mySQL Host: <?php echo $dbhost ?> &nbsp;
mySQL Database: <?php echo $dbname ?> &nbsp;
mySQL Username: <?php echo $dbusername ?> &nbsp;
mySQL Password: <?php echo (($dbpassword!='demopass' ? "(Set, but not shown for security reasons)" : "demopass") )?> &nbsp;
</pre>

<?php
require_once (dirname(__FILE__)."/$install_dirout/$core_subdir/_functions.php" ); // db funcs
require_once (dirname(__FILE__)."/$install_dirout/$core_subdir/_functions_cats.php" );
require_once (dirname(__FILE__)."/$install_dirout/$core_subdir/_functions_bposts.php" );

$new_db_version = 8040;				// next time: 8050

function create_b2evo_tables()
{
	global $tableposts, $tableusers, $tablesettings, $tablecategories, $tablecomments, $tableblogs,
        $tablepostcats, $tablehitlog, $tableantispam, $tablepluginsettings;
	global $baseurl, $new_db_version;

	echo "<p>Creating the necessary tables in the database...</p>";

	
	echo "<p>Creating table for Settings...<br />\n";
	$query = "CREATE TABLE $tablesettings ( 
		ID tinyint(3) DEFAULT '1' NOT NULL, 
		posts_per_page int(4) unsigned DEFAULT '7' NOT NULL, 
		what_to_show varchar(5) DEFAULT 'days' NOT NULL, 
		archive_mode varchar(10) DEFAULT 'weekly' NOT NULL, 
		time_difference tinyint(4) DEFAULT '0' NOT NULL, 
		AutoBR tinyint(1) DEFAULT '1' NOT NULL, 
		time_format varchar(20) DEFAULT 'H:i:s' NOT NULL, 
		date_format varchar(20) DEFAULT 'Y/m/d' NOT NULL, 
		db_version INT DEFAULT $new_db_version NOT NULL, 
		PRIMARY KEY (ID), 
		KEY ID (ID) 
	)";
	$q = mysql_query($query) or mysql_oops( $query );
	
	
	echo "Creating table for Users...<br />\n";
	$query = "CREATE TABLE $tableusers ( 
		ID int(10) unsigned NOT NULL auto_increment, 
		user_login varchar(20) NOT NULL, 
		user_pass CHAR(32) NOT NULL, 
		user_firstname varchar(50) NOT NULL, 
		user_lastname varchar(50) NOT NULL, 
		user_nickname varchar(50) NOT NULL, 
		user_icq int(10) unsigned DEFAULT '0' NOT NULL, 
		user_email varchar(100) NOT NULL, 
		user_url varchar(100) NOT NULL, 
		user_ip varchar(15) NOT NULL, 
		user_domain varchar(200) NOT NULL, 
		user_browser varchar(200) NOT NULL, 
		dateYMDhour datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
		user_level int(2) unsigned DEFAULT '0' NOT NULL, 
		user_aim varchar(50) NOT NULL, 
		user_msn varchar(100) NOT NULL, 
		user_yim varchar(50) NOT NULL, 
		user_idmode varchar(20) NOT NULL, 
		PRIMARY KEY (ID), 
		UNIQUE ID (ID), 
		UNIQUE (user_login) 
	)";
	$q = mysql_query($query) or mysql_oops( $query );
	

	echo "Creating table for Blogs...<br />\n";
	$query = "CREATE TABLE $tableblogs (
		blog_ID int(4) NOT NULL auto_increment,
		blog_shortname varchar(12) NULL default '',
		blog_name varchar(50) NOT NULL default '',
		blog_tagline varchar(250) NULL default '',
		blog_description varchar(250) NULL default '',
		blog_longdesc TEXT NULL DEFAULT NULL, 
		blog_lang VARCHAR(20) NOT NULL DEFAULT 'en_US',
		blog_siteurl varchar(120) NOT NULL default '$baseurl',
		blog_filename varchar(30) NULL default 'blog.php',
		blog_staticfilename varchar(30) NULL default NULL,
		blog_stub VARCHAR(30) NULL DEFAULT 'blog.php',
		blog_roll text,
		blog_keywords tinytext,
		blog_default_skin VARCHAR(30) NOT NULL DEFAULT 'standard',
		blog_UID VARCHAR(20),
		PRIMARY KEY  (blog_ID) 
	)";
	$q = mysql_query($query) or mysql_oops( $query );


	echo "Creating table for Categories...<br />\n";
	$query="CREATE TABLE $tablecategories (
		cat_ID int(4) NOT NULL auto_increment,
		cat_parent_ID int(11) default NULL,
		cat_name tinytext NOT NULL,
		cat_blog_ID int(11) NOT NULL default '2',
		cat_description VARCHAR(250) NULL DEFAULT NULL,
		cat_longdesc TEXT NULL DEFAULT NULL,
		cat_icon VARCHAR(30) NULL DEFAULT NULL,
		PRIMARY KEY  (cat_ID),
		KEY cat_blog_ID (cat_blog_ID),
		KEY cat_parent_ID (cat_parent_ID)
	)";
	$q = mysql_query($query) or mysql_oops( $query );
		
		
	echo "Creating table for Posts...<br />\n";
	$query = "CREATE TABLE $tableposts (   
		ID int(10) unsigned NOT NULL auto_increment,
		post_author int(4) NOT NULL default '0',
		post_date datetime NOT NULL default '0000-00-00 00:00:00',
		post_status enum('published','deprecated','protected','private','draft') NOT NULL default 'published',
		post_lang VARCHAR(20) NOT NULL DEFAULT 'en_US',
		post_content text NOT NULL,
		post_title text NOT NULL,
		post_urltitle VARCHAR(50) NULL DEFAULT NULL,
		post_url VARCHAR(250) NULL DEFAULT NULL,
		post_category int(4) NOT NULL default '0',
		post_trackbacks TEXT NULL DEFAULT NULL,  
		post_autobr tinyint(4) NOT NULL default '1',
		post_flags SET('pingsdone','pbdone','tbdone','html','bbcode','gmcode','smartquotes','smileys','glossary','imported'),
		post_karma int(11) NOT NULL default '0',
		post_wordcount int(11) default NULL,
		post_comments ENUM('disabled', 'open', 'closed') NOT NULL DEFAULT 'open',
		PRIMARY KEY (ID),
		KEY post_date (post_date),
		KEY post_category (post_category),
		KEY post_author (post_author),
		KEY post_status (post_status)
	)";
	$q = mysql_query($query) or mysql_oops( $query );


	echo "Creating table for Categories-to-Posts relationships...<br />\n";
	$query = "CREATE TABLE $tablepostcats (
		postcat_post_ID int(11) NOT NULL default '0',
		postcat_cat_ID int(11) NOT NULL default '0',
		PRIMARY KEY  (postcat_post_ID,postcat_cat_ID)
	)";
	$q = mysql_query($query) or mysql_oops( $query );


	echo "Creating table for Comments...<br />\n";
	$query = "CREATE TABLE $tablecomments ( 
		comment_ID int(11) unsigned NOT NULL auto_increment,
		comment_post_ID int(11) NOT NULL default '0',
		comment_type enum('comment','linkback','trackback','pingback') NOT NULL default 'comment',
		comment_status ENUM('published', 'deprecated', 'protected', 'private', 'draft') DEFAULT 'published' NOT NULL,
		comment_author tinytext NOT NULL,
		comment_author_email varchar(100) NOT NULL default '',
		comment_author_url varchar(100) NOT NULL default '',
		comment_author_IP varchar(100) NOT NULL default '',
		comment_date datetime NOT NULL default '0000-00-00 00:00:00',
		comment_content text NOT NULL,
		comment_karma int(11) NOT NULL default '0',
		PRIMARY KEY  (comment_ID),
		KEY comment_post_ID (comment_post_ID),
		KEY comment_date (comment_date),
		KEY comment_type (comment_type)
	 )";
	$q = mysql_query($query) or mysql_oops( $query );
	
	
	echo "Creating table for Hit-Logs...<br />\n";
	$query = "CREATE TABLE $tablehitlog (
		visitID bigint(11) NOT NULL auto_increment,
		visitTime timestamp(14) NOT NULL,
		visitURL varchar(250) default NULL,
		hit_ignore enum('no','invalid','badchar','blacklist','rss','robot','search') NOT NULL default 'no',
		referingURL varchar(250) default NULL,
		baseDomain varchar(250) default NULL,
		hit_blog_ID int(11) NOT NULL default '0',
		hit_remote_addr varchar(40) default NULL,
		hit_user_agent varchar(250) default NULL,
		PRIMARY KEY (visitID),
		KEY hit_ignore (hit_ignore),
		KEY baseDomain (baseDomain),
		KEY hit_blog_ID (hit_blog_ID),
		KEY hit_user_agent (hit_user_agent)
	)";
	$q = mysql_query($query) or mysql_oops( $query );
	 
	
	echo "Creating table for Anti-Spam Ban List...</p>\n";
	create_antispam();

#	echo "Creating table for Plugin Settings...</p>\n";
#	create_pluginsettings();

	echo "<p>All tables created successfully.</p>\n";
}

function populate_blogroll( & $now, $cat_blogroll_b2evo, $cat_blogroll_contrib)
{
	global $timestamp;

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Graham', 'Testing', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://tin-men.net/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Ron', 'Hacks, Testing', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://www.rononline.nl/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Topanga', 'Testing', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://www.tenderfeelings.be/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Swirlee', 'Development', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://swirlee.org/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Jason', 'Hosting', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://www.thejasonmurphyshow.com/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Travis', 'Hosting, Development', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://www.fromthecrossroads.ws/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Sakichan', 'Development, Testing', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://blog.sakichan.org/ja/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'Fran�ois', 'Main dev', $now, $cat_blogroll_contrib, array(), 'published',  'en', '', 0, true, 'http://fplanque.net/Blog/' ) or mysql_oops( $query );

	// Insert a post into blogroll:
	$now = date('Y-m-d H:i:s',$timestamp++);
	bpost_create( 1, 'b2evolution', 'Project home', $now, $cat_blogroll_b2evo, array(), 'published',  'en', '', 0, true, 'http://b2evolution.net/' ) or mysql_oops( $query );

}

function create_antispam()
{
	global $tableantispam;
	
	$query = "CREATE TABLE $tableantispam (
		ID bigint(11) NOT NULL auto_increment,
		domain varchar(250) NOT NULL,
		PRIMARY KEY (ID),
		KEY domain (domain)
	)";
	$q = mysql_query($query) or mysql_oops( $query );
}

function populate_antispam()
{
	global $tableantispam;
	
	$query = "INSERT INTO $tableantispam VALUES ('', 'prescriptions.md')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'penis-enlargement')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'online-casino')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'order-viagra')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'order-phentermine')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'order-xenical')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'order-prophecia')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'sexy-lingerie')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', '-porn-')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', '-adult-')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', '-tits-')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'mneuron.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'buy-phentermine')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'order-cheap-pills')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'menguma.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'gdough.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'online-hgh.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'pcfamily.ch')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'nositeyet.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'boris.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'mishka.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'sexgine.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'pornwizzard.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'zdenka.accepted.cc')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'bigstorysite.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'girlpron.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'agedwife.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'pissingpleasures.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'tvojproblem.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'slavebabe.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'dragoonguardsband.co.uk')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'shop.cokecans.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'bolinat.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'freenudecelebrity.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'www2.kedgy.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'buy-xenadrine.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'shatteredreality.net')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'paris-hilton')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'search4hardcore.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'livehomewebcams.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'findpink.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'hornyhamster.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'bobbakazoo.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'answerbus.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'blackfiles.hpg.ig.com.br')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'lodgingreservationcenter.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'payshots.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'killerdick.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'impai.org')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'toiletphoto.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'transsexualpalace.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'morganfinancialgroup.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'parishilton')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'collegecamgirls.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'adult-models.biz')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'busty2.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'agnitum.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'swedishteen.net')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'free.ynsbbs.com.ru')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'pimpvision.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'baltimorejewishtimes.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', '123adultsearch.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'girls.twistys.net')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'visitcelebrities.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'bushflash.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'buechercrawler.de')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'enyamodel.com')";
	mysql_query($query) or mysql_oops( $query );
	
	
	/*
	These may look innocent and human-made, but they are in fact clever tricks.
	They all spammed my referer logs on the same day, in rapid succession.
	*/
	$query = "INSERT INTO $tableantispam VALUES ('', 'veronicabee.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'evesmith.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'jennyknicks.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'princessnina.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'tawnygirl.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'skipme.com')";
	mysql_query($query) or mysql_oops( $query );
	$query = "INSERT INTO $tableantispam VALUES ('', 'doctorfem.com')";
	mysql_query($query) or mysql_oops( $query );
	
}

function create_pluginsettings()
{
	global $tablepluginsettings;
}


dbconnect() or die( "<p>Could not connect to database! Check you settings in /conf/b2eco_config.php!</p>" );

param( 'action', 'string' );
param( 'skins', 'integer', 0 );

$timestamp = time();

if( $skins )
{
	$stub_all = 'blog_all';
	$stub_a = 'blog_a';
	$stub_b = 'blog_b';
	$stub_roll = 'blog_roll';
}
else
{
	$stub_all = 'noskin_all';
	$stub_a = 'noskin_a';
	$stub_b = 'noskin_b';
	$stub_roll = 'noskin_roll';
}

switch( $action )
{
	case 'newdb':
		/* 
		 * -----------------------------------------------------------------------------------
		 * NEW DB: Create a plain new db structure + sample contents
		 * -----------------------------------------------------------------------------------
		 */
		?>
		<h3>Installing b2evolution tables with sample data</h3>
		<?php
		create_b2evo_tables();

		echo "<p>Creating sample contents...</p>\n";
		
		blog_create( 'All Blogs', 'All', '', $stub_all.'.php', $stub_all.'.php', $stub_all.'.html', 'Tagline for All', 'All blogs on this system.', NULL, $default_language,  "This is the blogroll for the \'all blogs\' blog aggregation.", 'all blogs keywords', '' ) or mysql_oops( $query );

		blog_create( 'Demo Blog A', 'Blog A', '', $stub_a.'.php', $stub_a.'.php', $stub_a.'.html', 'Tagline for A', 'This is demo blog A', 'This is description for demo blog A. It has index #2 in the database.', $default_language, 'This is the blogroll for Blog A...', 'blog A keywords', '' ) or mysql_oops( $query );
		
		blog_create( 'Demo Blog B', 'Blog B', '', $stub_b.'.php', $stub_b.'.php', $stub_b.'.html', 'Tagline for B', 'This is demo blog B', 'This is description for demo blog B. It has index #3 in the database.', $default_language, 'This is the blogroll for Blog B...', 'blog B keywords', '') or mysql_oops( $query );

		blog_create( 'Demo Blogroll', 'Blogroll', '', $stub_roll.'.php', $stub_roll.'.php', $stub_roll.'.html', 'Tagline for Blogroll', 'This is the demo blogroll', 'This is description for blogroll. It has index #4 in the database.', $default_language, 'This is the blogroll for the blogroll... pretty funky huh? :))', 'blogroll keywords', '') or mysql_oops( $query );

		echo "<p>blogs: OK<br />\n";
		
		// Create categories for blog A
		$cat_ann_a = cat_create( "Announcements [A]", 'NULL', 2 )  or mysql_oops( $query );
		$cat_news = cat_create( "News", 'NULL', 2 )  or mysql_oops( $query );
		$cat_bg = cat_create( "Background", 'NULL', 2 )  or mysql_oops( $query );
		
		// Create categories for blog B
		$cat_ann_b = cat_create( "Announcements [B]", 'NULL', 3 )  or mysql_oops( $query );
		$cat_fun = cat_create( "Fun", 'NULL', 3 )  or mysql_oops( $query );
		$cat_life = cat_create( "In real life", $cat_fun, 3 )  or mysql_oops( $query );
		$cat_web = cat_create( "On the web", $cat_fun, 3 )  or mysql_oops( $query );
		$cat_sports = cat_create( "Sports", $cat_life, 3 )  or mysql_oops( $query );
		$cat_movies = cat_create( "Movies", $cat_life, 3 )  or mysql_oops( $query );
		$cat_music = cat_create( "Music", $cat_life, 3 )  or mysql_oops( $query );
		$cat_b2evo = cat_create( "b2evolution", 'NULL', 3 )  or mysql_oops( $query );
		echo "categories: OK<br />\n";

		// Create categories for blogroll
		$cat_blogroll_b2evo = cat_create( "b2evolution", 'NULL', 4 )  or mysql_oops( $query );
		$cat_blogroll_contrib = cat_create( "contributors", 'NULL', 4 )  or mysql_oops( $query );
		
	
		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, 'First Post', '<p>This is the first post.</p>
		
		<p>It appears on both blog A and blog B.</p>', $now, $cat_ann_a, array( $cat_ann_b ) ) or mysql_oops( $query );
		
		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, 'Second post', '<p>This is the second post.</p>
		
		<p>It appears on blog A only but in multiple categories.</p>', $now, $cat_news, array( $cat_ann_a, $cat_bg ) ) or mysql_oops( $query );
		
		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, 'Third post', '<p>This is the third post.</p>
		
		<p>It appears on blog B only and in a single category.</p>', $now, $cat_fun ) or mysql_oops( $query );
		
		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, "Matrix Reloaded", "<p>Wait until the end of the super long end credits!</p>
		
		<p>If you're patient enough, you'll a get preview of the next episode...</p>
		
		<p>Though... it's just the same anyway! :>></p>", $now, $cat_movies ) or mysql_oops( $query );

		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, "Clean Permalinks! :idea:", "<p>b2evolution uses old-style permalinks and feedback links by default. This is to ensure maximum compatibility with various webserver configurations. Nethertheless, if you feel comfortable, you should try activating clean permalinks in the /conf/_advanced.php file...</p>", $now, $cat_b2evo ) or mysql_oops( $query );

		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, "Clean Skin! :idea:", "<p>By default, b2evolution blogs are displayed in the \'standard\' skin.</p>

<p>Readers can choose a new skin by using the skin switcher integrated in most skins.</p>		

<p>You can restrict available skins by deleting some of them from the /blogs/skins folder. You can also change the default skin or force a specific skin. <strong>Actually, you should change the default skin and delete the standard skin, as this one has navigation links at the top that are only good for the sake of the demo. These would be a nonsense on production servers!</strong> Read the manual on evoSkins!</p>", $now, $cat_b2evo ) or mysql_oops( $query );
		
		// POPULATE THE BLOGROLL:
		populate_blogroll( $now, $cat_blogroll_b2evo, $cat_blogroll_contrib );

		echo "posts: OK<br />\n";
		

			
		$now = date('Y-m-d H:i:s');
		$query = "INSERT INTO $tablecomments (comment_ID, comment_post_ID, comment_type, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_content, comment_karma)
		VALUES (1, 1, 'comment', 'miss b2', 'missb2@example.com', 'http://example.com', '127.0.0.1', '$now', 'Hi, this is a comment.<br />To delete a comment, just log in, and view the posts\' comments, there you will have the option to edit or delete them.', 0)";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "comments: OK<br />\n";
		

		// Populate the anti-spam table:
		populate_antispam();

		echo "anti-spam: OK<br />\n";

		
		// SETTINGS!
		$query = "INSERT INTO $tablesettings ( ID, posts_per_page, what_to_show, archive_mode, time_difference, AutoBR, time_format, date_format, db_version) VALUES ( '1', 3, 'paged', 'monthly', '0', '1', 'H:i:s', 'd.m.y', $new_db_version)";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "settings: OK<br />\n";
		
		
		
		
		$random_password = substr(md5(uniqid(microtime())),0,6);
		$query = "INSERT INTO $tableusers (ID, user_login, user_pass, user_firstname, user_lastname, user_nickname, user_icq, user_email, user_url, user_ip, user_domain, user_browser, dateYMDhour, user_level, user_aim, user_msn, user_yim, user_idmode) VALUES ( '1', 'admin', '".md5($random_password)."', '', '', 'admin', '0', '$admin_email', '', '127.0.0.1', '127.0.0.1', '', '00-00-0000 00:00:01', '10', '', '', '', 'nickname')";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "users: OK</p>";
			
		
		?>
		
		<p>Installation successful !</p>
		<br />
		Now you can <a href="../admin/b2edit.php">log in</a> with the login "admin" and password "<?php echo $random_password; ?>".<br />
		<br />
		<br />
		Note that password carefully ! It is a <em>random</em> password that is given to you when you install b2. If you lose it, you will have to delete the tables from the database yourself, and re-install b2.
	<?php
	break;


	case 'evodb':
		/*
		 * -----------------------------------------------------------------------------------
		 * EVO UPGRADE: Upgrade data from existing b2evolution database
		 * -----------------------------------------------------------------------------------
		 */
		?>
		<h3>Upgrading data in existing b2evolution database</h3>
		<?php
		
		// start benchmarking
		$time_start = gettimeofday();

		echo "<p>Checking DB schema version... ";
		$query = "SELECT db_version FROM $tablesettings WHERE ID = 1";
		$q = mysql_query($query) or mysql_oops( $query );
		$row = mysql_fetch_assoc($q);
		if( !isset($row['db_version'] ) ) die( 'NOT FOUND! This is not a b2evolution database.' );
		$old_db_version = $row['db_version'];
		echo $old_db_version, ' : ';
		if( $old_db_version < 8000 ) die( 'This version is too old!' );
		if( $old_db_version > $new_db_version ) die( 'This version is too recent! We cannot downgrade to it!' );
		echo "OK.<br />\n";

		// Note: version number 8000 once meant 0.8.00.0, but I decided to switch to sequential 
		// increments of 10 (in case we ever need to introduce intermediate versions for intermediate
		// bug fixes...)

		if( $old_db_version == $new_db_version )
		{
			echo '<p>The database schema is already up to date. There is nothing to do.</p>';
			echo '<p>You can <a href="../admin/b2edit.php">log in</a> with your usual b2 username and password.</p>';
			break;
		}

		if( $old_db_version < 8010 )
		{
			echo "<p>Upgrading users table... ";
			$query = "ALTER TABLE $tableusers 
								MODIFY COLUMN user_pass CHAR(32) NOT NULL";
			$q = mysql_query($query) or mysql_oops( $query );
			echo "OK.<br />\n";

			echo "<p>Upgrading blogs table... ";
			$query = "ALTER TABLE $tableblogs 
								MODIFY COLUMN blog_lang VARCHAR(20) NOT NULL DEFAULT 'en_US',
								MODIFY COLUMN blog_longdesc TEXT NULL DEFAULT NULL";
			$q = mysql_query($query) or mysql_oops( $query );
			echo "OK.<br />\n";

			echo "<p>Upgrading categories table... ";
			$query = "ALTER TABLE $tablecategories 
								ADD COLUMN cat_description VARCHAR(250) NULL DEFAULT NULL,
								ADD COLUMN cat_longdesc TEXT NULL DEFAULT NULL,
								ADD COLUMN cat_icon VARCHAR(30) NULL DEFAULT NULL";
			$q = mysql_query($query) or mysql_oops( $query );
			echo "OK.<br />\n";

			echo "<p>Upgrading posts table... ";
			$query = "ALTER TABLE $tableposts 
								MODIFY COLUMN post_lang VARCHAR(20) NOT NULL DEFAULT 'en_US',
								ADD COLUMN post_urltitle VARCHAR(50) NULL DEFAULT NULL AFTER post_title,
								ADD COLUMN post_url VARCHAR(250) NULL DEFAULT NULL AFTER post_urltitle,
								ADD COLUMN post_comments ENUM('disabled', 'open', 'closed') NOT NULL DEFAULT 'open' AFTER post_wordcount";
			$q = mysql_query($query) or mysql_oops( $query );
			echo "OK.<br />\n";

			echo "<p>Generating wordcounts... ";
			$query = "SELECT ID, post_content FROM $tableposts WHERE post_wordcount IS NULL";
			$q = mysql_query($query) or mysql_oops( $query );
			$rows_updated = 0;
			while($row = mysql_fetch_assoc($q)) 
			{
				$query_update_wordcount = "UPDATE $tableposts SET post_wordcount = " . bpost_count_words($row['post_content']) . " WHERE ID = " . $row['ID'];
				$q_update_wordcount = mysql_query($query_update_wordcount) or mysql_oops( $query_update_wordcount );
				$rows_updated++;
			}
			echo "OK. ($rows_updated rows updated)</p>\n";
		}


		if( $old_db_version < 8020 )
		{
			echo "<p>Encoding passwords... ";
			$query = "UPDATE $tableusers 
								SET user_pass = MD5(user_pass)";
			$q = mysql_query($query) or mysql_oops( $query );
			echo "OK.<br />\n";
		}

		if( $old_db_version < 8030 )
		{
			echo "<p>Deleting unecessary logs... ";
			$query = "DELETE FROM $tablehitlog
								WHERE hit_ignore IN ('badchar', 'blacklist')";
			$q = mysql_query($query) or mysql_oops( $query );
			echo "OK.<br />\n";

			echo "<p>Updating blog urls... ";
			$query = "SELECT blog_ID, blog_siteurl FROM $tableblogs";
			$q = mysql_query($query) or mysql_oops( $query );
			$rows_updated = 0;
			while($row = mysql_fetch_assoc($q)) 
			{
				$blog_ID = $row['blog_ID'];
				$blog_siteurl = $row['blog_siteurl'];
				// echo $blog_siteurl;
				if( strpos( $blog_siteurl, $baseurl ) !== 0 )
				{	// If not found at position 0
					echo ' <strong>WARNING: please check blog #', $blog_ID, ' manually.</strong> ';
					continue;
				}
				// crop off the baseurl:
				$blog_siteurl = substr( $blog_siteurl, strlen( $baseurl) );
				// echo ' -> ', $blog_siteurl,'<br />';

				$query_update_blog = "UPDATE $tableblogs SET blog_siteurl = '$blog_siteurl' WHERE blog_ID = $blog_ID";
				// echo $query_update_blog, '<br>';
				mysql_query($query_update_blog) or mysql_oops( $query_update_wordcount );
				$rows_updated++; 
			}
			echo "OK. ($rows_updated rows updated)</p>\n";

		}
	
		if( $old_db_version < 8040 )
		{
			echo "<p>Creating Anti-Spam Ban List... ";
			create_antispam();
			echo "OK.<br />\n";
			
			echo "<p>Populating Anti-Spam table... ";
			populate_antispam();
			echo "OK.<br />\n";

		}	               
			
		if( $old_db_version < 8050 )
		{
			/* 
			 * CONTRIBUTORS: If you need some more changes, put them here!
			 */
			#	echo "<p>Creating plugin settings table... ";
			#	create_pluginsettings();
			#	echo "OK.<br />\n";
		}
		
		// $new_db_version = 8001; // FOR TESTING
		echo "<p>Update DB schema version to $new_db_version... ";
		$query = "UPDATE $tablesettings SET db_version = $new_db_version WHERE ID = 1";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";
				
		// end benchmarking
		$time_end = gettimeofday();
		$time_total = (float)($time_end['sec'] - $time_start['sec']) + ((float)($time_end['usec'] - $time_start['usec'])/1000000);
		$time_total = round($time_total, 3);
		?>
		<p>Upgrade completed successfully! (<?php echo $time_total; ?> seconds)</p>

		<p>Now you can <a href="../admin/b2edit.php">log in</a> with your usual b2evolution username and password.</p>
		
 <?php
	break;


	case 'upgradedb':
		/* 
		 * -----------------------------------------------------------------------------------
		 * B2 UPGRADE: Create a new db structure + copy content from previous b2
		 * -----------------------------------------------------------------------------------
		 */
		?>
		<h3>Installing b2evolution tables and copying existing b2 data</h3>
		<?php
		
		// start benchmarking
		$time_start = gettimeofday();
		
		create_b2evo_tables();

		echo "<p>Creating default blogs...</p>\n";
		
		blog_create( 'All Blogs', 'All', '', $stub_all.'.php', $stub_all.'.php', $stub_all.'.html', 'Tagline for All', 'All blogs on this system.', NULL, $default_language,  "This is the blogroll for the \'all blogs\' blog aggregation.", 'all blogs keywords', '' ) or mysql_oops( $query );

		blog_create( 'My Upgraded Blog', 'Upgraded', '', $stub_a.'.php', $stub_a.'.php', $stub_a.'.html', 'Tagline for A', 'Upgraded blog - no description yet', 'This is description for your upgraded blog. It has index #2 in the database.', $default_language, 'This is the blogroll for Upgraded Blog...', '', '' ) or mysql_oops( $query );
		
		blog_create( 'Demo Blog B', 'Blog B', '', $stub_b.'.php', $stub_b.'.php', $stub_b.'.html', 'Tagline for B', 'This is demo blog B', 'This is description for demo blog B. It has index #3 in the database.', $default_language, 'This is the blogroll for Blog B...', 'blog B keywords', '') or mysql_oops( $query );

		blog_create( 'Demo Blogroll', 'Blogroll', '', $stub_roll.'.php', $stub_roll.'.php', $stub_roll.'.html', 'Tagline for Blogroll', 'This is the demo blogroll', 'This is description for blogroll. It has index #4 in the database.', $default_language, 'This is the blogroll for the blogroll... pretty funky huh? :))', 'blogroll keywords', '') or mysql_oops( $query );

		echo "<p>blogs: OK<br />\n";
		

		echo "<p>Copying data from original b2 tables...</p>\n";

		echo "<p>Copying settings... ";	
		// forcing paged mode because this works so much better !!!
		// You can always change it back in the options if you don't like it.
		$query = "INSERT INTO $tablesettings( ID, posts_per_page, what_to_show, archive_mode, time_difference, AutoBR, time_format, date_format, db_version) SELECT ID, 5, 'paged', archive_mode, time_difference, AutoBR, time_format, date_format, $new_db_version FROM $oldtablesettings";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";
		
		echo "Copying users... ";
		$query = "INSERT INTO $tableusers(" .
						"ID, user_login, user_pass, user_firstname, user_lastname, user_nickname, user_icq, user_email, " .
						"user_url, user_ip, user_domain, user_browser, dateYMDhour, user_level,	user_aim, user_msn, user_yim, user_idmode" .
					") SELECT " .
						"ID, user_login, MD5(user_pass), user_firstname, user_lastname, user_nickname, user_icq, user_email, " .
						"user_url, user_ip, user_domain, user_browser, dateYMDhour, user_level,	user_aim, user_msn, user_yim, user_idmode " .
						"FROM $oldtableusers";
		
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";
		
		echo "Copying categories... ";
		$query = "INSERT INTO $tablecategories( cat_ID, cat_parent_ID, cat_name, cat_blog_ID ) SELECT DISTINCT cat_ID, NULL, cat_name, 2 FROM $oldtablecategories";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";

		echo "Creating additionnal categories for Blog B... ";
		$cat_ann_b = cat_create( "Announcements [B]", 'NULL', 3 )  or mysql_oops( $query );
		$cat_fun = cat_create( "Fun", 'NULL', 3 )  or mysql_oops( $query );
		$cat_life = cat_create( "In real life", $cat_fun, 3 )  or mysql_oops( $query );
		$cat_web = cat_create( "On the web", $cat_fun, 3 )  or mysql_oops( $query );
		$cat_sports = cat_create( "Sports", $cat_life, 3 )  or mysql_oops( $query );
		$cat_movies = cat_create( "Movies", $cat_life, 3 )  or mysql_oops( $query );
		$cat_music = cat_create( "Music", $cat_life, 3 )  or mysql_oops( $query );
		$cat_b2evo = cat_create( "b2evolution", 'NULL', 3 )  or mysql_oops( $query );

		// Create categories for blogroll
		$cat_blogroll_b2evo = cat_create( "b2evolution", 'NULL', 4 )  or mysql_oops( $query );
		$cat_blogroll_contrib = cat_create( "contributors", 'NULL', 4 )  or mysql_oops( $query );
		echo "OK.<br />\n";
		
		echo "Copying posts... ";
		$query = "INSERT INTO $tableposts( ID, post_author, post_date, post_status, post_lang, post_content,post_title, post_category, post_autobr, post_flags, post_karma)  SELECT ID, post_author, post_date, 'published', '$default_language', post_content, post_title, post_category, 1, 'pingsdone,html,imported', post_karma FROM $oldtableposts";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";

		echo "Generating wordcounts... ";
		$query = "SELECT ID, post_content FROM $tableposts";
		$q = mysql_query($query) or mysql_oops( $query );
		
		$rows_updated = 0;
		
		while($row = mysql_fetch_assoc($q)) {
			$query_update_wordcount = "UPDATE $tableposts SET post_wordcount = " . bpost_count_words($row['post_content']) . " WHERE ID = " . $row['ID'];
			$q_update_wordcount = mysql_query($query_update_wordcount) or mysql_oops( $query_update_wordcount );
			
			$rows_updated++;
		}
		
		echo "OK. ($rows_updated rows updated)<br />\n";

		echo "Generating postcats... ";
 		$query = "INSERT INTO $tablepostcats( postcat_post_ID, postcat_cat_ID ) SELECT ID, post_category FROM $tableposts";		
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";

		echo "Creating a few additionnal samples for Blog B... ";
		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, 'Sample post', '<p>This is a sample post.</p>
		
		<p>It appears on blog B only and in a single category.</p>', $now, $cat_fun ) or mysql_oops( $query );
		
		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, "Matrix Reloaded", "<p>Wait until the end of the super long end credits!</p>
		
		<p>If you're patient enough, you'll a get preview of the next episode...</p>
		
		<p>Though... it's just the same anyway! :>></p>", $now, $cat_movies ) or mysql_oops( $query );
				

		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, "Clean Permalinks! :idea:", "<p>b2evolution uses old-style permalinks and feedback links by default. This is to ensure maximum compatibility with various webserver configurations. Nethertheless, if you feel comfortable, you should try activating clean permalinks in the /conf/_advanced.php file...</p>", $now, $cat_b2evo ) or mysql_oops( $query );

		// Insert a post:
		$now = date('Y-m-d H:i:s',$timestamp++);
		bpost_create( 1, "Clean Skin! :idea:", "<p>By default, b2evolution blogs are displayed in the \'standard\' skin.</p>

<p>Readers can choose a new skin by using the skin switcher integrated in most skins.</p>		

<p>You can restrict available skins by deleting some of them from the /blogs/skins folder. You can also change the default skin or force a specific skin. <strong>Actually, you should change the default skin and delete the standard skin, as this one has navigation links at the top that are only good for the sake of the demo. These would be a nonsense on production servers!</strong> Read the manual on evoSkins!</p>", $now, $cat_b2evo ) or mysql_oops( $query );

		// POPULATE THE BLOGROLL:
		populate_blogroll( $now, $cat_blogroll_b2evo, $cat_blogroll_contrib );

		echo "OK.<br />\n";

		
		echo "Copying comments... ";
		$query = "INSERT INTO $tablecomments( comment_ID, comment_post_ID, comment_type, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_content, comment_karma ) SELECT comment_ID, comment_post_ID, 'comment', comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_content, comment_karma FROM $oldtablecomments";
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";

		echo "Qualifying comments... Trackback...";
 		$query = "UPDATE $tablecomments SET comment_type = 'trackback' WHERE comment_content LIKE '<trackback />%'";		
		$q = mysql_query($query) or mysql_oops( $query );
		echo "Qualifying comments... Pingback...";
 		$query = "UPDATE $tablecomments SET comment_type = 'pingback' WHERE comment_content LIKE '<pingback />%'";		
		$q = mysql_query($query) or mysql_oops( $query );
		echo "OK.<br />\n";

		echo "Creating Anti-Spam Ban list...";
		create_antispam();
		echo "OK.<br />\n";
		echo "Populating Anti-Spam table...";
		populate_antispam();
		echo "OK.<br />\n";
	#	echo "<p>Creating plugin settings table... ";
	#	create_pluginsettings();
	#	echo "OK.<br />\n";
		

		// end benchmarking
		$time_end = gettimeofday();
		$time_total = (float)($time_end['sec'] - $time_start['sec']) + ((float)($time_end['usec'] - $time_start['usec'])/1000000);
		$time_total = round($time_total, 3);
?>
		<p>Upgrade completed successfully! (<?php echo $time_total; ?> seconds)</p>
		
		<p>Now you can <a href="../admin/b2edit.php">log in</a> with your usual b2 username and password.</p>

		
<?php		
	break;


	case 'deletedb':
		/* 
		 * -----------------------------------------------------------------------------------
		 * DELETE DB: Delete the db structure!!! (Everything will be lost)
		 * -----------------------------------------------------------------------------------
		 */
		?>
		
		<h3>Deleting b2evolution tables from the datatase</h3>

		<?php
		if( $allow_evodb_reset != 1 )
		{
			?>
			<p>For security reasons, the reset feature is disabled by default.</p>
			<p>To enable it, please go back the /conf/_config.php file and change:</p>
			<pre>$allow_evodb_reset = 0;</pre>
			to
			<pre>$allow_evodb_reset = 1;</pre>
			<p>Then reload this page and resetting will take place.</p>

			<p>Back to <a href="install.php">menu</a>.</p>
			<?php
			break;	
		}
		
		echo "Droping Plugin Settings table...<br />\n";
		$query = "DROP TABLE IF EXISTS $tablepluginsettings";
		$q = mysql_query($query) or mysql_oops( $query );

		echo "Droping Anti-Spam table...<br />\n";
		$query = "DROP TABLE IF EXISTS $tableantispam";
		$q = mysql_query($query) or mysql_oops( $query );

		echo "Droping Hit-Logs...<br />\n";
		$query = "DROP TABLE IF EXISTS $tablehitlog";
		$q = mysql_query($query) or mysql_oops( $query );
		
		echo "Droping Comments...<br />\n";
		$query = "DROP TABLE IF EXISTS $tablecomments";
		$q = mysql_query($query) or mysql_oops( $query );

		echo "Droping Categories-to-Posts relationships...<br />\n";
		$query = "DROP TABLE IF EXISTS $tablepostcats";
		$q = mysql_query($query) or mysql_oops( $query );

		echo "Droping Categories...<br />\n";
		$query = "DROP TABLE IF EXISTS $tablecategories";
		$q = mysql_query($query) or mysql_oops( $query );
		
		echo "Droping Posts...<br />\n";
		$query = "DROP TABLE IF EXISTS $tableposts";
		$q = mysql_query($query) or mysql_oops( $query );

		echo "Droping Users...<br />\n";
		$query = "DROP TABLE IF EXISTS $tableusers";
		$q = mysql_query($query) or mysql_oops( $query );

		echo "Droping Blogs...<br />\n";
		$query = "DROP TABLE IF EXISTS $tableblogs";
		$q = mysql_query($query) or mysql_oops( $query );

		echo "Droping Settings...</p>\n";
		$query = "DROP TABLE IF EXISTS $tablesettings";
		$q = mysql_query($query) or mysql_oops( $query );

		?>
		
		<p>Reset done!</p>

		<p>Back to <a href="install.php">menu</a>.</p>
		 
		 <?php
		 		
	break;


	default:
		/* 
		 * -----------------------------------------------------------------------------------
		 * Menu
		 * -----------------------------------------------------------------------------------
		 */
?>

	<h3>What do you want to install?</h3>
		
	<form action="install.php" method="post">

	<fieldset>
		<legend>The database tables installation can be done in different ways. Choose one:</legend>

 <p>The delete feature enables you to come back here later and start anew with a different option, so feel free to experiment :)</p>
 	
<p><input type="radio" name="action" value="newdb" checked="checked"> <strong>New Install</strong>: Install b2evolution tables with sample data.</p>
	
		<p><input type="radio" name="action" value="evodb"> <strong>Upgrade from a previous version of b2evolution</strong>: This will upgrade your b2evolution database in order to make it compatible with the current version!</p>

  	<p><input type="radio" name="action" value="upgradedb"> <strong>Upgrade from original b2</strong>: Install b2evolution tables and copy your existing b2 data into them. </p>

  	<p><input type="radio" name="action" value="deletedb"> <strong>Delete b2evolution tables</strong>: If you have installed b2evolution tables before and wish to start anew, you must delete the b2evolution tables before you can start a new installation. <strong>WARNING: All your b2evolution tables and data will be lost!!!</strong> Your original b2 tables though, if you have some, will not be touched.</p>
		
	</fieldset>

	<fieldset>
		<legend>What display system would you like to be activated by default?</legend>
	
		<p><input type="radio" name="skins" value="1" checked="checked"> <strong>evoSkins</strong>: Blogs will be displayed with a variety of skins you can choose from at reading time. (Recommended)</p>

		<p><input type="radio" name="skins" value="0"> <strong>templates</strong>: Blogs will be displayed in an example template. (This is how the original b2 used to work)</p>
		
		
		<p>This setting just sets a default for displaying the blogs right after the install. You can fully customize this later. You can even display some blogs with evoSkins and some blogs with templates... So don't worry too much about what you choose here ;)</p>


	</fieldset>
	

	<p><input type="submit" value="Install Database Tables Now !" /></p>
	</form>

 
  <p><strong>Note for original b2 users</strong>: Feel safe: Unless you explicitely decided to use the same names in the advanced config, your original b2 tables and data will <strong>NEVER</strong> by modified by b2evolution. b2evolution can only <strong>copy</strong> their contents.</p>
  <?php 
}
?>


<!-- InstanceEndEditable -->
<div id="rowfooter">
<a href="http://b2evolution.net/">official website</a> &middot; <a href="http://b2evolution.net/about/license.html">GNU GPL license</a> &middot; <a href="http://fplanque.net/About/index.html">contact: Fran&ccedil;ois PLANQUE</a>
</div>

</body>
<!-- InstanceEnd --></html>
