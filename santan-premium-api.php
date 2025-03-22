<?php
/*
Plugin Name: Santan Premium API
Description: Santan Premium API.
Version: 1.0
Requires PHP: 7.4
Author: Taigo Ito
Author URI: https://qwel.design/
License: GNU General Public License v3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */


defined( 'ABSPATH' ) || exit;


class Santan_Premium_API {
		
	public function __construct() {
		// テーマサポート機能
		$this->setup_theme();

	}

	public function setup_theme() {
    // カスタム投稿タイプ, タクソノミーを登録
    add_action( 'init', [ $this, 'register_product_as_post_type' ] );
    
    // カスタムフィールドを作成
    add_action( 'admin_menu', [ $this, 'create_meta_boxes' ] );

    // カスタムフィールドの値を保存
    add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
    
    // REST API にて表示
    add_action( 'rest_api_init', [ $this, 'register_product_attrs' ] );

  }

  public function register_product_as_post_type() {
    $name = '商品';
    register_post_type( 'product', [
      'labels' => [
        'name'           => $name,
        'singular_name'  => $name,
        'menu_name'      => '商品',
        'all_items'      => $name . '一覧',
        'new_item'       => '新規' . $name,
        'add_new_item'   => '新規' . $name . 'を追加',
        'edit_item'      => $name . 'を編集',
        'view_item'      => $name . 'を表示',
        'search_items'   => $name . 'を検索'
      ],
      'public'         => true,
      'has_archive'    => false,
      'menu_icon'      => 'dashicons-database',
      'menu_position'  => 25,
      'show_in_rest'   => true,
      'supports'       => [
        'title',
        'thumbnail'
      ]
    ] );

    register_taxonomy( 'group','product', [
      'labels' => [
        'name' => 'グループ'
      ],
      'hierarchical' => true,
      'show_in_rest' => true,
    ] );
  }

  public function create_meta_boxes() {
    add_meta_box(
      'product-setting',
      '商品設定',
      [ $this, 'insert_meta_boxes' ],
      'product'
    );
  }

  public function insert_meta_boxes() {
    global $post;
    // 商品単価
    $product_price = get_post_meta( $post->ID, 'product_price', true );
    // カートURL
    $cart_id = get_post_meta( $post->ID, 'cart_id', true );
?>
  
<form method="post" action="admin.php?page=site_settings">
  <label for="productPrice">商品単価: </label>
  <input id="productPrice" type="number" name="productPrice" value="<?php echo $product_price ?>">
  <label for="endDate">カートID: </label>
  <input id="cartID" type="text" name="cartID" value="<?php echo $cart_id ?>">
</form>
  
<?php
  }

  public function save_meta_boxes( $post_id ) {
    if ( isset( $_POST[ 'productPrice' ] ) ) {
      update_post_meta( $post_id, 'product_price', $_POST[ 'productPrice' ] );
    }
    if ( isset( $_POST[ 'cartID' ] ) ) {
      update_post_meta( $post_id, 'cart_id', $_POST[ 'cartID' ] );
    }
  }

  public function register_product_attrs() {
    register_rest_field( 'product', 'attributes', [
      'get_callback' => [ $this, 'get_product_attrs' ]
    ] );
  }

  public function get_product_attrs( $object ) {
		$product_name  = get_the_title( $post_id );
		$product_image = get_the_post_thumbnail_url( $post_id );
    $product_price = get_post_meta( $object[ 'id' ], 'product_price', true );
    $cart_id       = get_post_meta( $object[ 'id' ], 'cart_id', true );
    $groups        = get_the_terms( $object[ 'id' ], 'group' );
    return [
      'product_name'   => $product_name,
      'product_image'  => $product_image,
      'product_price'  => $product_price,
      'cart_id'        => $cart_id,
      'groups'         => $groups
    ];
  }

}

new Santan_Premium_API();
