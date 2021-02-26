<?php
/**
* Abstract class to defined a general mapping interface for form -> post.
* @since 5.0.0
*/

abstract class Form_2_Post_Mapper {
  /**
	 * The properties of the mapped custom post type.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_properties    an array of properties.
	 */
  protected $post_properties=array();
  /**
	 * The properties of the mapped custom taxonomy.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $taxonomy_properties    an array of properties with   $taxonomy_slug=>array('source'=>$taxonomy_source, 'singular_name'=>$value, 'name'=>$plural_name)
	 */
  protected $taxonomy_properties=array();
  /**
	 * The the CF7 post ID.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      int    $cf7_post_ID    the CF7 post ID.
	 */
  public $cf7_post_ID=0;
  /**
   * The the CF7 post unique key.
   *
   * @since    1.2.7
   * @access    protected
   * @var      string    $cf7_key    the unique key which can be used to identfy this form.
   */
  public $cf7_key;
  /**
	 * The CF7 form fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $cf7_form_fields    an array containing CF7 fields, {'field name'=>'field type'}.
	 */
  protected $cf7_form_fields = array();
  /**
	 * The CF7 form fields options.
	 *
	 * @since    2.0.0
	 * @access    protected
	 * @var      array    $cf7_form_fields_options    an array containing CF7 field name and its array of options, {'field name'=>array()}.
	 */
  protected $cf7_form_fields_options =array();
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_fields    an array mapped CF7 fields, to default
   * post fields {'form field name'=>'post field'}.
	 */
  protected $post_map_fields = array();
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_meta_fields    an array mapped CF7 fields, to post
   * custom meta fields  {'form field name'=>'post field'}.
	 */
  protected $post_map_meta_fields=array();
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_taxonomy    an array mapped CF7 fields, to post
   * custom taxonomy  {'form field name'=>'taxonomy slug'}.
	 */
   protected $post_map_taxonomy=array();
  /**
	 * The CF7 form fields values to set as localize_script.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $localise_values    an array CF7 fields=>values.
	 */
  protected $localise_values;
  /**
   * An array of wpcf7_type taxonomy terms this form belongs to.
   * @since 3.2.0
   * @access protected
   * @var array $form_terms an array of terms.
   **/
   protected $form_terms=array();
   /**
   * an array of post_id=>array($post_type =>[factory|system])); key value pairs.
   * @since 3.4.0
   * @access protected
   * @var array $mapped_post_types an array of key value pairs.
   */
   protected static $mapped_post_types=array();
   /**
   * boolean flag to flush rewrite rules when custom posts are created/updated.
   * @since 3.8.2
   * @access protected
   * @var boolean $flush_permalink_rules a boolean flag.
   */
   protected $flush_permalink_rules = false;
   /**
   * an array of existing system post types to which a form can be mapped.
   * @since 5.0.0
   * @access protected
   * @var array $mapped_post_types an array of key value pairs.
   */
   protected static $system_post_types=array();
  /**
   * Default Construct
   *
   * @since    1.0.0
   * @param    int    $cf7_post_id the ID of the CF7 form.
   */
  /**
   * Strips mapped fields from the admin $_POST form saving.
   *
   * @since 5.0.0
   * @param      String    $field_prefix     field prefix used to identify where this field is mapped to.
   * @return     Array    array of fields mapped to post values.
  **/
  protected function get_mapped_fields($field_prefix){
    $prefix_length = strlen($field_prefix);
    $fields = array();
    foreach($_POST as $field => $value){
      if(empty($value)) continue;
      if (0===strpos($field, $field_prefix)){
        $post_field = substr($field, $prefix_length);
        $fields[sanitize_text_field($value)]=$post_field;
      }
    }
    return $fields;
  }
  protected function _save_post_fields(&$old_fields){
    //save post fields
    $this->post_map_fields = $this->get_mapped_fields('cf7_2_post_map-');
    //debug_msg($this->post_map_fields, 'saving post fields');
    foreach($this->post_map_fields as $cf7_field=>$post_field){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map-'.$post_field,$cf7_field);
      if(isset($old_fields['cf7_2_post_map-'.$post_field]) ){
        unset($old_fields['cf7_2_post_map-'.$post_field]);
      }
    }
  }
  protected function _save_post_meta_fields(&$old_fields){
    $this->post_map_meta_fields = $this->get_mapped_fields('cf7_2_post_map_meta_value-');
    //debug_msg($this->post_map_meta_fields, 'saving meta fields');
    foreach($this->post_map_meta_fields as $cf7_field=>$post_field){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_meta-'.$post_field,$cf7_field);
      if(isset($old_fields['cf7_2_post_map_meta-'.$post_field]) ){
        unset($old_fields['cf7_2_post_map_meta-'.$post_field]);
      }
    }
  }
  protected function _save_taxonomy_fields(&$old_fields){
    /*
    the taxonomy field names are built using the slug, such as
      cf7_2_post_map_taxonomy_names-<taxonomy_slug>
      so we need to keep track of the field name prefix length to strip the slug
    */
    $fields = get_mapped_fields('cf7_2_post_map_taxonomy_');
    $len_c2p_taxonomy = strlen('value-');
    $len_c2p_name = strlen('name-');
    $len_c2p_names = strlen('names-');
    $len_c2p_source = strlen('source-');
    //keep track of all the taxonomy slugs in the the slugs array
    $slugs=array();
    $post_map_taxonomy = array();
    foreach($fields as $value=>$field){
      switch(true){
        case (0 === strpos($field,'source-') ): //taxonomy source.
          $slug = substr($field,$len_c2p_source);
          if( !isset($this->taxonomy_properties[$slug]) ){
            $this->taxonomy_properties[$slug] =  array();
          }
          $this->taxonomy_properties[$slug]['source'] = $value;
          break;
        case (0 === strpos($field,'names-') ): //Plural name.
          $slug = substr($field,$len_c2p_names);
          if( !isset($this->taxonomy_properties[$slug]) ){
            $this->taxonomy_properties[$slug] =  array();
          }
          $this->taxonomy_properties[$slug]['name'] = $value;
          //debug_msg("POST FIELD: ".$value."=".substr($field,$len_cf7_2_post_map_meta));
          break;
        case (0 === strpos($field,'name-') ): //singular name.
          $slug = substr($field,$len_c2p_name);
          if( !isset($this->taxonomy_properties[$slug]) ){
            $this->taxonomy_properties[$slug] =  array();
          }
          $this->taxonomy_properties[$slug]['singular_name'] = $value;
          //debug_msg("POST FIELD: ".$value."=".substr($field,$len_cf7_2_post_map_meta));
          break;
        case (0 === strpos($field,'slug-') ): //taxonomy slug.
          if( !isset($this->taxonomy_properties[$value]) ){
            $this->taxonomy_properties[$value] =  array();
          }
          $slugs[]= $value;
          break;
        case (0 === strpos($field,'value-') ): //form field mapped to taxonomy.
          $slug = substr($field,$len_c2p_taxonomy);
          $post_map_taxonomy[$value] = $slug;
          //debug_msg("POST FIELD: ".$value."=".substr($field,$len_cf7_2_post_map_meta));
          break;
      }
    }
    //save the taxonomy mappings so they can be created
    foreach($slugs as $slug){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      if(isset($old_fields['cf7_2_post_map_taxonomy_source-'.$slug]) ){
        unset($old_fields['cf7_2_post_map_taxonomy_source-'.$slug]);
        unset($old_fields['cf7_2_post_map_taxonomy_names-'.$slug]);
        unset($old_fields['cf7_2_post_map_taxonomy_name-'.$slug]);
      }
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_source-'.$slug,$this->taxonomy_properties[$slug]['source']);
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_names-'.$slug,$this->taxonomy_properties[$slug]['name']);
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_name-'.$slug,$this->taxonomy_properties[$slug]['singular_name']);
    }

    //debug_msg($slugs, "saved taxonomy ");
    //save the taxonomy properties
    $this->post_properties['taxonomy'] = array_merge($this->post_properties['taxonomy'],$slugs );
    //make sure they are unique
    $this->post_properties['taxonomy'] = array_unique($this->post_properties['taxonomy']);
    update_post_meta($this->cf7_post_ID, '_cf7_2_post-taxonomy', $this->post_properties['taxonomy']);
    //map the cf7 fields to the taxonomies
    foreach($post_map_taxonomy as $cf7_field=>$taxonomy){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy-'.$taxonomy,$cf7_field);
    }
    $this->post_map_taxonomy = array_merge($this->post_map_taxonomy, $post_map_taxonomy);

    return true;
  }
  public function save_mapping(){
    //taxonomy associated with this post type for this mapping.
    $this->post_properties['taxonomy'] = array();
    //set the version of this plugin.
    $this->post_properties['version'] = CF7_2_POST_VERSION;
    //set post type.
    $this->post_properties['type'] = sanitize_text_field( $_POST['mapped_post_type']);
    //current status of mapper.
    $this->post_properties['map']= sanitize_text_field($_POST['c2p_mapped_status']);//'publish'|'draft';
    //setup additional properties.
    $this->set_post_properties();
    //save properties to DB.
    $old_cf7_post_metas = get_post_meta($this->cf7_post_ID); //track old settings to remove surplus.
    foreach($this->post_properties as $key=>$value){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, '_cf7_2_post-'.$key,$value);
      //clear previous values.
      if(isset($old_cf7_post_metas['_cf7_2_post-'.$key]) ){
        unset($old_cf7_post_metas['_cf7_2_post-'.$key]);
      }
    }
    //save post fields
    $this->_save_post_fields($old_cf7_post_metas);
    //save meta fields
    $this->_save_post_meta_fields($old_cf7_post_metas);
    $this->_save_taxonomy_fields($old_cf7_post_metas);

    //clear any surplus old values.
    foreach($old_cf7_post_metas as $key=>$value){
      switch(true){
        case (0 === strpos($key,'_cf7_2_post-')):
        case (0 === strpos($key,'cf7_2_post_map-')):
        case (0 === strpos($key,'cf7_2_post_map_meta-')):
        case (0 === strpos($key,'cf7_2_post_map_taxonomy_source-')):
        case (0 === strpos($key,'cf7_2_post_map_taxonomy_names-')):
        case (0 === strpos($key,'cf7_2_post_map_taxonomy_name-')):
          delete_post_meta($this->cf7_post_ID, $key);
          break;
      }
    }
    //set flush rules flag. @since 3.8.2.
    update_post_meta($this->cf7_post_ID,'_cf7_2_post_flush_rewrite_rules', $this->flush_permalink_rules);
    
    return true;
  }
  abstract protected function set_post_properties();
}
