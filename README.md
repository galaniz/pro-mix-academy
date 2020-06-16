# Pro Mix Academy

WordPress child theme for Pro Mix Academy

#### Shortcodes

#### `get-posts`

Get and display posts.

_Parameters:_

* `type`  
_Type:_ `string`  
_Default:_ `course`

* `posts_per_page`  
_Type:_ `int`  
_Default:_ `10`

* `layout`  
_Type:_ `string`  
_Default:_ `''`  
_Possible Values:_ `table, cards_v, cards_h, cards_flex`  

* `gradient` - add bottom gradient to image in `cards_v` layout  
_Type:_ `boolean`  
_Default:_ `false`  

* `category_slug` - add tax query   
_Type:_ `string`  
_Default:_ `''`

* `mentor_id` - for single courses    
_Type:_ `int`  
_Default:_ `0`

* `meta_key` - add meta query  
_Type:_ `string`  
_Default:_ `''`

* `meta_value` - add meta query (works better with string values particularly with serialized data)   
_Type:_ `string`  
_Default:_ `''`

* `meta_type` - add meta query      
_Type:_ `string`  
_Default:_ `string`  
_Possible Values:_ `string, int, int-array, string-array`

* `show_content` - for `cards_flex` layout used for testimonials   
_Type:_ `boolean`  
_Default:_ `true`   

* `horizontal` - for `cards_flex` layout used for testimonials   
_Type:_ `boolean`  
_Default:_ `true`   

* `like_archive`   
_Type:_ `boolean`  
_Default:_ `false`

* `ids` - comma separated list of post ids   
_Type:_ `string`  
_Default:_ `''`

_Returns:_ `string` of markup
