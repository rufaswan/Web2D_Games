function QuadVerify(Q){
	var $ = this; // public
	var __ = {};  // private

	//////////////////////////////

	__.ATTR = 0;

	// case insensitive tag matching
	__.object_lowercase_keys = function( obj ){
		if ( typeof obj !== 'object' )
			return;
		if ( Array.isArray(obj) )
			return;

		Object.keys(obj).forEach(function(tag){
			var low = tag.toLowerCase();

			// already lower case
			if ( tag === low )
				return;
			// have lower/upper/mixed case
			if ( typeof obj[low] !== 'undefined' )
				return Q.func.error('QuadVerify', 'dupkey', tag, low);

			// move to lower case
			obj[low] = obj[tag];
		});
	}

	__.attr_bitflag = function( attrib, enumlist ){
		if ( Array.isArray(attrib) ){
			var bitflag = 0;
			for ( var i=0; i < attrib.length; i++ )
				bitflag |= __.attr_bitflag(attrib[i], enumlist);
			return bitflag;
		}
		if ( typeof attrib === 'string' ){
			var idx = enumlist.indexOf(attrib);
			if ( idx < 0 ){
				idx = enumlist.length;
				enumlist.push(attrib);
			}
			return (1 << idx);
		}
		return 0;
	}

	__.is_str = function( str ){
		if ( typeof str !== 'string' )
			return false;
		return ( str.length > 0 );
	}

	__.is_str_array = function( arr, num ){
		if ( ! Array.isArray(arr) )
			return false;
		if ( arr.length !== num )
			return false;
		for ( var i=0; i < num; i++ ){
			if ( ! __.is_str(arr[i]) )
				return false;
		}
		return true;
	}

	__.is_num_array = function( arr, num ){
		if ( ! Array.isArray(arr) )
			return false;
		if ( arr.length !== num )
			return false;
		for ( var i=0; i < num; i++ ){
			if ( typeof arr[i] !== 'number' )
				return false;
		}
		return true;
	}

	//////////////////////////////

	__.verify_blend = function( obj, id ){
		var def = {
			name       : 'blend ' + id,
			mode_rgb   : 0,
			mode_alpha : 0,
			color      : [1,1,1,1],
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			switch ( tag ){
				case 'name':
					if ( __.is_str(obj.name) )
						def.name = obj.name;
					break;
				case 'mode_rgb':
				case 'mode_alpha':
					if ( ! __.is_str_array( obj[tag], 3) )
						break;
					def[tag] = Q.gl.is_gl_enum( obj[tag] );
					break;
				case 'color':
					def.color = Q.math.css_color(obj.color);
					break;
			} // switch ( tag )
		});

		if ( ! def.mode_rgb )
			return 0;
		if ( ! def.mode_alpha )
			def.mode_alpha = def.mode_rgb;
		return def;
	}

	__.verify_slot = function( obj, id ){
		if ( ! Array.isArray(obj) )
			return 0;
		var def = [];
		obj.forEach(function(ov,ok){
			var t = __.verify_attach(ov);
			if ( t )
				def.push(t);
		});
		return ( def.length > 0 ) ? def : 0;
	}

	__.verify_hitbox_layer = function( obj ){
		var def = {
			debug     : 0,
			hitquad   : 0,
			attribute : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'hitquad':
					if ( ! __.is_num_array(obj.hitquad, 8) ) // 4 xy
						break;
					def.hitquad = obj.hitquad;
					break;
				case 'attribute':
					def.attribute = __.attr_bitflag(obj.attribute, __.ATTR.hitbox);
					break;
			} // switch(tag)
		});

		if ( ! def.hitquad )
			return 0;
		return def;
	}

	__.verify_hitbox = function( obj, id ){
		var def = {
			debug  : 0,
			name   : 'hitbox ' + id,
			layer  : [],
			__RECT : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			if ( tag[0] === '_' )
				return;
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'name':
					if ( __.is_str(obj.name) )
						def.name = obj.name;
					break;
				case 'layer':
					if ( ! Array.isArray(obj.layer) )
						break;
					obj.layer.forEach(function(v,k){
						def.layer[k] = __.verify_keyframe_layer(v);
					});
					obj.layer = 0;
					break;
			} // switch(tag)
		});

		return def;
	}

	__.verify_keyframe_layer = function( obj ){
		var def = {
			debug     : 0,
			dstquad   : 0,
			srcquad   : 0,
			fogquad   : [1,1,1,1 , 1,1,1,1 , 1,1,1,1 , 1,1,1,1],
			tex_id    : -1,
			blend_id  : -1,
			attribute : 0,
			colorize  : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'dstquad':
				case 'srcquad':
					if ( ! __.is_num_array(obj[tag], 8) ) // 4 xy
						break;
					def[tag] = obj[tag];
					break;
				case 'fogquad':
					if ( __.is_str(obj.fogquad) ){
						var c = Q.math.css_color(obj.fogquad);
						def.fogquad = [].concat(c, c, c, c);
						break;
					}
					if ( __.is_str_array(obj.fogquad, 4) ){
						var c0 = Q.math.css_color( obj.fogquad[0] );
						var c1 = Q.math.css_color( obj.fogquad[1] );
						var c2 = Q.math.css_color( obj.fogquad[2] );
						var c3 = Q.math.css_color( obj.fogquad[3] );
						def.fogquad = [].concat(c0, c1, c2, c3);
						break;
					}
					break;
				case 'tex_id':
				case 'blend_id':
					if ( typeof obj[tag] === 'number' )
						def[tag] = obj[tag] | 0;
					break;
				case 'attribute':
					def.attribute = __.attr_bitflag(obj.attribute, __.ATTR.keyframe);
					break;
				case 'colorize':
					def.colorize  = __.attr_bitflag(obj.colorize , __.ATTR.colorize);
					break;
			} // switch(tag)
		});

		if ( ! def.dstquad )
			return 0;
		return def;
	}

	__.verify_keyframe = function( obj, id ){
		var def = {
			debug  : 0,
			name   : 'keyframe ' + id,
			layer  : [],
			order  : [],
			__RECT : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			if ( tag[0] === '_' )
				return;
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'name':
					if ( __.is_str(obj.name) )
						def.name = obj.name;
					break;
				case 'layer':
					if ( ! Array.isArray(obj.layer) )
						break;
					obj.layer.forEach(function(v,k){
						def.layer[k] = __.verify_keyframe_layer(v);
					});
					obj.layer = 0;
					break;
				case 'order':
					if ( ! Array.isArray(obj.order) )
						break;
					if ( ! __.is_num_array(obj.order, obj.order.length) )
						break;
					def.order = obj.order;
					break;
			} // switch(tag)
		});

		if ( def.order.length < 1 ){
			for ( var i=0; i < def.layer.length; i++ )
				def.order.push(i);
		}
		return def;
	}

	__.verify_animation_timeline = function( obj ){
		var def = {
			debug        : 0,
			time         : -1,
			attach       : 0,
			matrix       : 0,
			color        : [1,1,1,1],
			matrix_mix   : 0,
			color_mix    : 0,
			keyframe_mix : 0,
			hitbox_mix   : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'time':
					if ( typeof obj.time === 'number' )
						def.time = obj.time | 0;
					break;
				case 'attach':
					def.attach = __.verify_attach(obj.attach);
					break;
				case 'matrix':
					if ( ! __.is_num_array(obj.matrix, 16) ) // 4x4 matrix
						break;
					def.matrix = obj.matrix;
					break;
				case 'color':
					def.color = Q.math.css_color(obj.color);
					break;
				case 'matrix_mix':
				case 'color_mix':
				case 'keyframe_mix':
				case 'hitbox_mix':
					def[tag] = obj[tag] | 0;
					break;
			} // switch(tag)
		});

		if ( def.time < 1 )
			return 0;
		return def;
	}

	__.verify_animation = function( obj, id ){
		var def = {
			debug    : 0,
			name     : 'animation ' + id,
			loop_id  : -1,
			timeline : [],
			__RECT   : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			if ( tag[0] === '_' )
				return;
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'name':
					if ( __.is_str(obj.name) )
						def.name = obj.name;
					break;
				case 'loop_id':
					if ( typeof obj.loop_id === 'number' )
						def.loop_id = obj.loop_id | 0;
					break;
				case 'timeline':
					if ( ! Array.isArray(obj.timeline) )
						break;
					obj.timeline.forEach(function(v){
						var t = __.verify_animation_timeline(v);
						if ( t )
							def.timeline.push(t);
					});
					obj.timeline = 0;
					break;
			} // switch(tag)
		});

		return def;
	}

	__.verify_skeleton_bone = function( obj ){
		var def = {
			debug  : 0,
			attach : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'attach':
					def.attach = __.verify_attach(obj.attach);
					break;
			} // switch(tag)
		});
		return def;
	}

	__.verify_skeleton = function( obj, id ){
		var def = {
			debug  : 0,
			name   : 'skeleton ' + id,
			bone   : [],
			__RECT : 0,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		Object.keys(def).forEach(function(tag){
			if ( tag[0] === '_' )
				return;
			switch(tag){
				case 'debug':
					def.debug = JSON.stringify(obj.debug || 0);
					break;
				case 'name':
					if ( __.is_str(obj.name) )
						def.name = obj.name;
					break;
				case 'bone':
					if ( ! Array.isArray(obj.bone) )
						break;
					obj.bone.forEach(function(v,k){
						def.bone[k] = __.verify_skeleton_bone(v);
					});
					obj.bone = 0;
					break;
			} // switch(tag)
		});

		return def;
	}

	//////////////////////////////

	__.verify_attach = function( obj ){
		var def = {
			type : '',
			id   : -1,
		};
		if ( typeof def !== typeof obj )
			return 0;
		__.object_lowercase_keys(obj);

		if ( ! __.is_str(obj.type) )
			return 0;
		def.type = obj.type.toLowerCase();

		if ( typeof obj.id === 'number' )
			def.id = obj.id | 0;
		if ( def.id < 0 )
			return 0;
		return def;
	}

	__.verify_each = function( tag, obj ){
		var valid = [];
		var func  = 'verify_' + tag;
		if ( ! __[func] )
			return 0;

		var none  = true;
		obj.forEach(function(v,k){
			var t = __[func](v,k);
			if ( t ){
				none = false;
				valid[k] = t;
			}
		});
		return ( none ) ? 0 : valid;
	}

	$.verify_quadfile = function( quad ){
		var valid = {
			blend     : [],
			slot      : [],
			hitbox    : [],
			keyframe  : [],
			animation : [],
			skeleton  : [],
			__MIX     : [],
			__ATTR    : {
				keyframe : [],
				hitbox   : [],
				colorize : [],
			},
		};
		if ( typeof valid !== typeof quad )
			return valid;
		__.object_lowercase_keys(quad);

		__.ATTR = valid.__ATTR;
		Object.keys(valid).forEach(function(tag){
			if ( tag[0] === '_' )
				return;
			if ( ! Array.isArray( quad[tag] ) )
				return;
			valid[tag] = __.verify_each( tag, quad[tag] );
			quad[tag]  = 0;
		});

		valid.tag = quad.tag || 0;
		return valid;
	}

	//////////////////////////////

} // QuadVerify
