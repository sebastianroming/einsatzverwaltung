<?
/*
Plugin Name: Einsatzverwaltung
Plugin URI: http://www.abrain.de
Description: Verwaltung von Feuerwehreinsätzen
Version: 0.0.1
Author: Andreas Brain
Author URI: http://www.abrain.de
License: GPLv2
*/

add_action( 'init', 'create_post_type' );

function create_post_type() {
	$args_einsatz = array(
		'labels' => array(
   			'name' => 'Einsatzberichte',
   			'singular_name' => 'Einsatzbericht',
   			'menu_name' => 'Einsatzberichte',
   			'add_new' => 'Neu',
   			'add_new_item' => 'Neuer Einsatzbericht',
   			'edit' => 'Bearbeiten',
   			'edit_item' => 'Einsatzbericht bearbeiten',
   			'new_item' => 'Neuer Einsatzbericht',
   			'view' => 'Ansehen',
   			'view_item' => 'Einsatzbericht ansehen',
   			'search_items' => 'Einsatzberichte suchen',
   			'not_found' => 'Keine Einsatzberichte gefunden',
   			'not_found_in_trash' => 'Keine Einsatzberichte im Papierkorb gefunden'
   			),
		'public' => true,
		'has_archive' => true,
		'rewrite' => array(
		     'slug' => 'einsaetze',
		     'feeds' => true
		),
		'supports' => array('title', 'editor', 'thumbnail'),
		'show_in_nav_menus' => false,
		'menu_position' => 5);
	register_post_type( 'einsatz', $args_einsatz);
	
	$args_einsatzart = array(
	   'label' => 'Einsatzarten',
	   'labels' => array(
	       'name' => 'Einsatzarten',
	       'singular_name' => 'Einsatzart',
	       'menu_name' => 'Einsatzarten',
	       'all_items' => 'Alle Einsatzarten',
	       'edit_item' => 'Einsatzart bearbeiten',
	       'view_item' => 'Einsatzart ansehen',
	       'update_item' => 'Einsatzart aktualisieren',
	       'add_new_item' => 'Neue Einsatzart',
	       'new_item_name' => 'Einsatzart hinzufügen',
	       'search_items' => 'Einsatzarten suchen',
	       'popular_items' => 'Häufige Einsatzarten',
	       'separate_items_with_commas' => 'Einsatzarten mit Kommata trennen',
	       'add_or_remove_items' => 'Einsatzarten hinzufügen oder entfernen',
	       'choose_from_most_used' => 'Aus häufigen Einsatzarten wählen'),
       'public' => true,
       'show_in_nav_menus' => false);
	register_taxonomy( 'einsatzart', 'einsatz', $args_einsatzart );
	
	$args_fahrzeug = array(
	   'label' => 'Fahrzeuge',
	   'labels' => array(
	       'name' => 'Fahrzeuge',
	       'singular_name' => 'Fahrzeug',
	       'menu_name' => 'Fahrzeuge',
	       'all_items' => 'Alle Fahrzeuge',
	       'edit_item' => 'Fahrzeug bearbeiten',
	       'view_item' => 'Fahrzeug ansehen',
	       'update_item' => 'Fahrzeug aktualisieren',
	       'add_new_item' => 'Neues Fahrzeug',
	       'new_item_name' => 'Fahrzeug hinzufügen',
	       'search_items' => 'Fahrzeuge suchen',
	       'popular_items' => 'Oft eingesetzte Fahrzeuge',
	       'separate_items_with_commas' => 'Fahrzeuge mit Kommata trennen',
	       'add_or_remove_items' => 'Fahrzeuge hinzufügen oder entfernen',
	       'choose_from_most_used' => 'Aus häufig eingesetzten Fahrzeugen wählen'),
       'public' => true,
       'show_in_nav_menus' => false);
	register_taxonomy( 'fahrzeug', 'einsatz', $args_fahrzeug );
	
	// more rewrite rules
	add_rewrite_rule('einsaetze/([0-9]{4})/?$', 'index.php?post_type=einsatz&year=$matches[1]', 'top');
}


function my_rewrite_flush() {
    // First, we "add" the custom post type via the above written function.
    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
    // They are only referenced in the post_type column with a post entry, 
    // when you add a post of this CPT.
    create_post_type();

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'my_rewrite_flush' );


add_action( 'admin_init', 'einsatz_admin' );

function einsatz_admin() {
	add_meta_box( 'einsatz_meta_box',
		'Einsatzdetails',
		'display_einsatz_meta_box',
		'einsatz', 'normal', 'high'
	);
}


/* Prints the box content */
function display_einsatz_meta_box( $post ) {
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'einsatzverwaltung_nonce' );

    // The actual fields for data entry
    // Use get_post_meta to retrieve an existing value from the database and use the value for the form
    $nummer = get_post_meta( $post->ID, $key = 'einsatz_nummer', $single = true );
    $alarmzeit = get_post_meta( $post->ID, $key = 'einsatz_alarmzeit', $single = true );
    $dauer = get_post_meta( $post->ID, $key = 'einsatz_dauer', $single = true );
    
    if(empty($nummer)) {
        
        $year = date('Y');
        $query = new WP_Query( 'year=' . $year .'&post_type=einsatz&post_status=publish&nopaging=true' );
        
        $nummer = $year.str_pad(($query->found_posts + 1), 3, "0", STR_PAD_LEFT);
    }


    echo '<table><tbody>';

    echo '<tr><td><label for="einsatzverwaltung_nummer">';
        _e("Einsatznummer", 'einsatzverwaltung_textdomain' );
    echo '</label></td>';
    echo '<td><input type="text" id="einsatzverwaltung_nummer" name="einsatzverwaltung_nummer" value="'.esc_attr($nummer).'" size="10" /></td></tr>';
    
    echo '<tr><td><label for="einsatzverwaltung_alarmzeit">';
        _e("Alarmzeit", 'einsatzverwaltung_textdomain' );
    echo '</label></td>';
    echo '<td><input type="text" id="einsatzverwaltung_alarmzeit" name="einsatzverwaltung_alarmzeit" value="'.esc_attr($alarmzeit).'" size="25" /> (YYYY-MM-DD hh:mm)</td></tr>';

    echo '<tr><td><label for="einsatzverwaltung_dauer">';
        _e("Dauer", 'einsatzverwaltung_textdomain' );
    echo '</label></td>';
    echo '<td><input type="text" id="einsatzverwaltung_dauer" name="einsatzverwaltung_dauer" value="'.esc_attr($dauer).'" size="6" /> Minuten</td></tr>';
    
    echo '</tbody></table>';
}

/* When the post is saved, saves our custom data */
function einsatzverwaltung_save_postdata( $post_id ) {

    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( !isset( $_POST['einsatzverwaltung_nonce'] ) || !wp_verify_nonce( $_POST['einsatzverwaltung_nonce'], plugin_basename( __FILE__ ) ) )
        return;

  
    // Check permissions
    if ( 'page' == $_POST['post_type'] ) 
    {
        if ( !current_user_can( 'edit_page', $post_id ) )
            return;
    }
    else
    {
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;
    }

    // OK, we're authenticated: we need to find and save the data
    
    //if saving in a custom table, get post_ID
    $post_ID = $_POST['post_ID'];
    //sanitize user input
    $data_nummer = sanitize_text_field( $_POST['einsatzverwaltung_nummer'] );
    $data_alarmzeit = sanitize_text_field( $_POST['einsatzverwaltung_alarmzeit'] );
    $data_dauer = sanitize_text_field( $_POST['einsatzverwaltung_dauer'] );
    
    add_post_meta($post_ID, 'einsatz_nummer', $data_nummer, true) or
    update_post_meta($post_ID, 'einsatz_nummer', $data_nummer);

    add_post_meta($post_ID, 'einsatz_alarmzeit', $data_alarmzeit, true) or
    update_post_meta($post_ID, 'einsatz_alarmzeit', $data_alarmzeit);
    
    add_post_meta($post_ID, 'einsatz_dauer', $data_dauer, true) or
    update_post_meta($post_ID, 'einsatz_dauer', $data_dauer);
    
    
    
    $my_args = array();
    $my_args['ID'] = $post_id;
    
    
    $alarmzeit_timestamp = strtotime($data_alarmzeit);
    if($alarmzeit_timestamp) {
        $my_args['post_date'] = date("Y-m-d H:i:s", $alarmzeit_timestamp);
    }
    
    if(!empty($data_nummer) && is_numeric($data_nummer)) {
        $my_args['post_name'] = $data_nummer;
    }
        
    if(array_key_exists('post_date', $my_args) || array_key_exists('post_name', $my_args)) {
        
        if ( ! wp_is_post_revision( $post_id ) ) {
    	
    		// unhook this function so it doesn't loop infinitely
    		remove_action('save_post', 'einsatzverwaltung_save_postdata');
    	   
    		// update the post, which calls save_post again
    		wp_update_post( $my_args );
    
    		// re-hook this function
    		add_action('save_post', 'einsatzverwaltung_save_postdata');
    	}
    }
}

add_action( 'save_post', 'einsatzverwaltung_save_postdata' );


function add_einsatz_daten($content) {
    global $post;
    
    if(get_post_type() == "einsatz") {
        $alarmzeit = get_post_meta($post->ID, 'einsatz_alarmzeit', true);
        
        $dauer = get_post_meta($post->ID, 'einsatz_dauer', true);
        if(empty($dauer) || !is_numeric($dauer)) {
            $dauerstring = "-";
        } else {
            if($dauer <= 0) {
                $dauerstring = "-";
            } else if($dauer < 60) {
                $dauerstring = $dauer." Minuten";
            } else {
                $dauer_h = intval($dauer / 60);
                $dauer_m = $dauer % 60;
                $dauerstring = $dauer_h." Stunde".($dauer_h > 1 ? "n" : "");
                if($dauer_m > 0) {
                    $dauerstring .= " ".$dauer_m." Minute".($dauer_m > 1 ? "n" : "");
                }
            }
        }
        
        $einsatzarten = get_the_terms( $post->ID, 'einsatzart' );
        if ( $einsatzarten && ! is_wp_error( $einsatzarten ) ) {
            $arten_namen = array();
            foreach ( $einsatzarten as $einsatzart ) {
                $arten_namen[] = $einsatzart->name;
            }
            $art = join( ", ", $arten_namen );
        } else {
            $art = "-";
        }
        
        $fahrzeuge = get_the_terms( $post->ID, 'fahrzeug' );
        if ( $fahrzeuge && ! is_wp_error( $fahrzeuge ) ) {
            $fzg_namen = array();
            foreach ( $fahrzeuge as $fahrzeug ) {
                $fzg_namen[] = $fahrzeug->name;
            }
            $fzg_string = join( ", ", $fzg_namen );
        } else {
            $fzg_string = "-";
        }
        
        $alarm_timestamp = strtotime($alarmzeit);
        $einsatz_datum = ($alarm_timestamp ? date("d.m.Y", $alarm_timestamp) : "-");
        $einsatz_zeit = ($alarm_timestamp ? date("H:i", $alarm_timestamp)." Uhr" : "-");
        $daten = "<strong>Datum:</strong> ".$einsatz_datum."<br>";
        $daten .= "<strong>Alarmzeit:</strong> ".$einsatz_zeit."<br>";
        $daten .= "<strong>Dauer:</strong> ".$dauerstring."<br>";
        $daten .= "<strong>Art:</strong> ".$art."<br>";
        $daten .= "<strong>Fahrzeuge:</strong> ".$fzg_string."<br>";
        
        $daten .= "<hr>";
        
        if(strlen($content) > 0) {
            $daten .= "<h3>Einsatzbericht:</h3>";
        } else {
            $daten .= "Kein Einsatzbericht vorhanden";
        }
        
        $content = $daten.$content;
    }
    
    return $content;
}
add_filter( 'the_content', 'add_einsatz_daten');


function einsatz_excerpt($excerpt)
{
    global $post;
    if(get_post_type() == "einsatz") {
        return "excerpt";
    }
    else {
        return $excerpt;
    }
}
add_filter( 'the_excerpt', 'einsatz_excerpt');


function replace_post_date_with_einsatz_date($time, $format)
{
    global $post;
    if(get_post_type() == "einsatz") {
        if($format == "j" || $format == "M") {
            $alarmzeit = get_post_meta($post->ID, 'einsatz_alarmzeit', true);
            $timestamp = strtotime($alarmzeit);
            return date($format, $timestamp);
        } else {
            return "??";
        }
    }
    return $time;
}
add_filter('the_time', 'replace_post_date_with_einsatz_date', 10, 2);





add_filter( 'manage_edit-einsatz_columns', 'my_edit_einsatz_columns' ) ;

function my_edit_einsatz_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Einsatzbericht' ),
		'e_nummer' => __( 'Nummer' ),
		'e_datum' => __( 'Datum' ),
		'e_dauer' => __( 'Dauer' ),
		'e_art' => __( 'Art' ),
		'e_fzg' => __( 'Fahrzeuge' )
	);

	return $columns;
}


add_action( 'manage_einsatz_posts_custom_column', 'my_manage_einsatz_columns', 10, 2 );

function my_manage_einsatz_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

        case 'e_nummer' :
            $einsatz_nummer = get_post_meta( $post_id, 'einsatz_nummer', true );

			if ( empty( $einsatz_nummer ) )
				echo __( '-' );
			else
				echo __( $einsatz_nummer );

			break;

        case 'e_dauer' :
            $einsatz_dauer = get_post_meta( $post_id, 'einsatz_dauer', true );

			if ( empty( $einsatz_dauer ) )
				echo __( '-' );
			else
				printf( __( '%s Minuten' ), $einsatz_dauer );

			break;
			
        case 'e_datum' :
            $einsatz_datum = get_post_meta( $post_id, 'einsatz_alarmzeit', true );
            $timestamp = strtotime($einsatz_datum);

			if ( empty( $einsatz_datum ) )
				echo __( '-' );
			else
    			echo __( date("d.m.Y", $timestamp)."<br>".date("H:i", $timestamp) );

			break;
			
        case 'e_art' :

			$terms = get_the_terms( $post_id, 'einsatzart' );

			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'einsatzart' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'einsatzart', 'display' ) )
					);
				}

				echo join( ', ', $out );
			}

			else {
				_e( '-' );
			}

			break;

        case 'e_fzg' :

			$terms = get_the_terms( $post_id, 'fahrzeug' );

			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'fahrzeug' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'fahrzeug', 'display' ) )
					);
				}

				echo join( ', ', $out );
			}

			else {
				_e( '-' );
			}

			break;

		default :
			break;
	}
}


add_shortcode( 'einsatzliste', 'print_einsatzliste' );
function print_einsatzliste( $atts )
{
    extract( shortcode_atts( array('jahr' => date('Y') ), $atts ) );

    $string = "";
    
    if (strlen($jahr)!=4 || !is_numeric($jahr)) {
        $aktuelles_jahr = date('Y');
        $string .= "INFO: Jahreszahl \"".$jahr."\" ung&uuml;ltig, verwende ".$aktuelles_jahr."<br>";
        $jahr = $aktuelles_jahr;
    }

    $query = new WP_Query( 'year=' . $jahr .'&post_type=einsatz&post_status=publish&nopaging=true' );

    if ( $query->have_posts() ) {
        $string .= "<table class=\"einsatzliste\">";
        $string .= "<thead><tr>";
        $string .= "<th>Nummer</th>";
        $string .= "<th>Datum</th>";
        $string .= "<th>Zeit</th>";
        $string .= "<th>Einsatzmeldung</th>";
        $string .= "</tr></thead>";
        $string .= "<tbody>";
        while ( $query->have_posts() ) {
            $query->next_post();
            
            $einsatz_nummer = get_post_meta($query->post->ID, 'einsatz_nummer', true);
            $alarmzeit = get_post_meta($query->post->ID, 'einsatz_alarmzeit', true);
            $einsatz_timestamp = strtotime($alarmzeit);
            
            $einsatz_datum = date("d.m.Y", $einsatz_timestamp);
            $einsatz_zeit = date("H:i", $einsatz_timestamp);
            
            $string .= "<tr>";
            $string .= "<td width=\"80\">".$einsatz_nummer."</td>";
            $string .= "<td width=\"80\">".$einsatz_datum."</td>";
            $string .= "<td width=\"50\">".$einsatz_zeit."</td>";
            $string .= "<td>";
            
            $post_title = get_the_title($query->post->ID);
            if ( !empty($post_title) ) {
                $string .= "<a href=\"".get_permalink($query->post->ID)."\" rel=\"bookmark\">".$post_title."</a><br>";
            } else {
                $string .= "<a href=\"".get_permalink($query->post->ID)."\" rel=\"bookmark\">(kein Titel)</a><br>";
            }
            $string .= "</td>";
            $string .= "</tr>";
        }
        
        $string .= "</tbody>";
        $string .= "</table>";
    } else {
        $string .= "Keine Eins&auml;tze";
    }
    
    return $string;
}

add_shortcode( 'einsatzjahre', 'print_einsatzjahre' );
function print_einsatzjahre( $atts )
{
    global $year;
    $jahre = array();
    $query = new WP_Query( '&post_type=einsatz&post_status=publish&nopaging=true' );
    while($query->have_posts()) {
        $p = $query->next_post();
        $timestamp = strtotime($p->post_date);
        $jahre[date("Y", $timestamp)] = 1;
    }
    
    $string = "";
    foreach (array_keys($jahre) as $jahr) {
        if(!empty($string)) {
            $string .= " | ";
        }
        $string .= "<a href=\"../../einsaetze/".$jahr."\">";
        if($year == $jahr || empty($year) && $jahr == date("Y")) $string .= "<strong>";
        $string .= $jahr;
        if($year == $jahr || empty($year) && $jahr == date("Y")) $string .= "</strong>";
        $string .= "</a>";
    }
    
    return $string;
}


##############################
#           WIDGET           #
##############################

class Einsatz_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'einsatz_widget', // Base ID
			'Letzte Einsätze', // Name
			array( 'description' => __( 'Zeigt die neuesten Einsätze an', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$anzahl = $instance['anzahl'];
		$zeigeDatum = $instance['zeigeDatum'];
		$zeigeZeit = $instance['zeigeZeit'];
		
		if ( empty( $title ) )
		  $title = "Letzte Einsätze";
		if ( !isset($anzahl) || empty ($anzahl) || !is_numeric($anzahl) || $anzahl < 1)
		  $anzahl = 3;

        $letzteEinsaetze = "";
		$query = new WP_Query( '&post_type=einsatz&post_status=publish&posts_per_page='.$anzahl );
        while($query->have_posts()) {
            $p = $query->next_post();
            $letzteEinsaetze .= "<li>";
            
            $letzteEinsaetze .= "<a href=\"".get_permalink($p->ID)."\" rel=\"bookmark\" class=\"einsatzmeldung\">";
            $meldung = get_the_title($p->ID);
            if ( !empty($meldung) ) {
                $letzteEinsaetze .= $meldung;
            } else {
                $letzteEinsaetze .= "(kein Titel)";
            }
            $letzteEinsaetze .= "</a>";
            
            if($zeigeDatum) {
                setlocale(LC_TIME, "de_DE");
                $timestamp = strtotime($p->post_date);
                $letzteEinsaetze .= "<br>";
                $letzteEinsaetze .= "<span class=\"einsatzdatum\">".strftime("%d. %b %Y", $timestamp)."</span>";
                if($zeigeZeit) {
                    $letzteEinsaetze .= " | <span class=\"einsatzzeit\">".date("H:i", $timestamp)." Uhr</span>";
                }
            }
            $letzteEinsaetze .= "</li>";
        }

		echo $before_widget;
		echo $before_title . $title . $after_title;
        echo ( empty($letzteEinsaetze) ? "Keine Einsätze" : "<ul>".$letzteEinsaetze."</ul>");
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		$anzahl = $new_instance['anzahl'];
        if ( empty ($anzahl) || !is_numeric($anzahl) || $anzahl < 1)
            $instance['anzahl'] = $old_instance['anzahl'];
        else
            $instance['anzahl'] = $new_instance['anzahl'];
        
        $instance['zeigeDatum'] = $new_instance['zeigeDatum'];
        $instance['zeigeZeit'] = $new_instance['zeigeZeit'];

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Letzte Einsätze', 'text_domain' );
		}
		
		if ( isset( $instance[ 'anzahl' ] ) ) {
			$anzahl = $instance[ 'anzahl' ];
		}
		else {
			$anzahl = 3;
		}
		
		$zeigeDatum = $instance[ 'zeigeDatum' ];
		$zeigeZeit = $instance[ 'zeigeZeit' ];
		
		echo "<p><label for=\"".$this->get_field_id( 'title' )."\">";
		_e( 'Title:' );
		echo "</label>";
		echo "<input class=\"widefat\" id=\"".$this->get_field_id( 'title' )."\" name=\"".$this->get_field_name( 'title' )."\" type=\"text\" value=\"".esc_attr( $title )."\" /></p>";
		
		echo "<p><label for=\"".$this->get_field_id( 'anzahl' )."\">";
		_e( 'Anzahl:' );
		echo "</label>";
		echo "<input id=\"".$this->get_field_id( 'anzahl' )."\" name=\"".$this->get_field_name( 'anzahl' )."\" type=\"text\" value=\"".$anzahl."\" size=\"3\" /></p>";

		echo "<p><input id=\"".$this->get_field_id( 'zeigeDatum' )."\" name=\"".$this->get_field_name( 'zeigeDatum' )."\" type=\"checkbox\" ".($zeigeDatum ? "checked=\"checked\" " : "")."/>";
		echo "&nbsp;<label for=\"".$this->get_field_id( 'zeigeDatum' )."\">";
		_e( 'Datum anzeigen' );
		echo "</label></p>";

		echo "<p>&nbsp;&nbsp;<input id=\"".$this->get_field_id( 'zeigeZeit' )."\" name=\"".$this->get_field_name( 'zeigeZeit' )."\" type=\"checkbox\" ".($zeigeZeit ? "checked=\"checked\" " : "")."/>";
		echo "&nbsp;<label for=\"".$this->get_field_id( 'zeigeZeit' )."\">";
		echo "Zeit anzeigen (nur in Kombination mit Datum)";
		echo "</label></p>";
	}
}

// register Einsatz_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "einsatz_widget" );' ) );


function remove_einsatz_menu( ) {
    $einsatz_authors = array(2,7);
	if ( !current_user_can( 'manage_options' ) && !in_array(get_current_user_id(), $einsatz_authors)  ) {
		remove_menu_page( 'edit.php?post_type=einsatz' );
	}
}
add_action( 'admin_menu', 'remove_einsatz_menu', 999 );

?>