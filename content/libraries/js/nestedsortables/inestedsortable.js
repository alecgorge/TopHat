/**
 * 
 * Nested Sortable Plugin for jQuery/Interface.
 * 
 * Version 1.0.1
 *  
 *Change Log:
 * 1.0 
 *       Initial Release
 * 1.0.1
 *       Added noNestingClass option to prevent nesting in some elements.
 *
 * Copyright (c) 2007 Bernardo de Padua dos Santos
 * Dual licensed under the MIT (MIT-LICENSE.txt) 
 * and GPL (GPL-LICENSE.txt) licenses.
 * 
 * http://code.google.com/p/nestedsortables/
 * 
 * Compressed using Dean Edwards' Packer (http://dean.edwards.name/packer/)
 * 
 */

jQuery.iNestedSortable = {
	checkHover: function (e, o) {
		if (e.isNestedSortable) {
			jQuery.iNestedSortable.scroll(e);
			return jQuery.iNestedSortable.newCheckHover(e);
		} else {
			return jQuery.iNestedSortable.oldCheckHover(e, o);
		}
	}, oldCheckHover: jQuery.iSort.checkhover,
	newCheckHover: function (e) {
		if (!jQuery.iDrag.dragged) {
			return;
		}
		if (!(e.dropCfg.el.size() > 0)) {
			return;
		}
		if (!e.nestedSortCfg.remeasured) {
			jQuery.iSort.measure(e);
			e.nestedSortCfg.remeasured = true;
		}
		var a = jQuery.iNestedSortable.findPrecedingItem(e);
		var b = jQuery.iNestedSortable.shouldNestItem(e, a);
		var c = !a ? jQuery.iNestedSortable.isTouchingFirstItem(e) : false;
		var d = false;
		if (a) {
			if (e.nestedSortCfg.lastPrecedingItem === a && e.nestedSortCfg.lastShouldNest === b) {
				d = true;
			}
		} else if (e.nestedSortCfg.lastPrecedingItem === a && e.nestedSortCfg.lastTouchingFirst === c) {
			d = true;
		}
		e.nestedSortCfg.lastPrecedingItem = a;
		e.nestedSortCfg.lastShouldNest = b;
		e.nestedSortCfg.lastTouchingFirst = c;
		if (d) {
			return;
		}
		if (a !== null) {
			if (b) {
				jQuery.iNestedSortable.nestItem(e, a);
			} else {
				jQuery.iNestedSortable.appendItem(e, a);
			}
		} else if (c) {
			jQuery.iNestedSortable.insertOnTop(e);
		}
	}, scroll: function (e) {
		if (!e.nestedSortCfg.autoScroll) {
			return false;
		}
		var a = e.nestedSortCfg.scrollSensitivity;
		var b = e.nestedSortCfg.scrollSpeed;
		var c = jQuery.iDrag.dragged.dragCfg.currentPointer;
		var d = jQuery.iUtil.getScroll();
		if (c.y - d.ih - d.t > -a) {
			window.scrollBy(0, b);
		}
		if (c.y - d.t < a) {
			window.scrollBy(0, -b);
		}
	}, check: function (a) {
		jQuery.iNestedSortable.newCheck(a);
		return jQuery.iNestedSortable.oldCheck(a);
	}, oldCheck: jQuery.iSort.check,
	newCheck: function (a) {
		if (jQuery.iNestedSortable.latestNestingClass && jQuery.iNestedSortable.currentNesting) {
			jQuery.iNestedSortable.currentNesting.removeClass(jQuery.iNestedSortable.latestNestingClass);
			jQuery.iNestedSortable.currentNesting = null;
			jQuery.iNestedSortable.latestNestingClass = "";
		}
		if (jQuery.iDrop.overzone.isNestedSortable) {
			jQuery.iDrop.overzone.nestedSortCfg.remeasured = false;
		}
	}, serialize: function (s) {
		if (jQuery("#" + s).get(0).isNestedSortable) {
			return jQuery.iNestedSortable.newSerialize(s);
		} else {
			return jQuery.iNestedSortable.oldSerialize(s);
		}
	}, oldSerialize: jQuery.iSort.serialize,
	newSerialize: function (s) {
		var i;
		var h = "";
		var j = "";
		var o = {};
		var e;
		var k = function (f) {
			var g = [];
			thisChildren = jQuery(f).children("." + jQuery.iSort.collected[s]);
			thisChildren.each(function (i) {
				var a = jQuery.attr(this, "id");
				if (a && a.match) {
					a = a.match(e.nestedSortCfg.serializeRegExp)[0];
				}
				if (h.length > 0) {
					h += "&";
				}
				h += s + j + ("[" + i + "][id]=" + a);
				g[i] = {
					id: a
				};
				var b = jQuery(this).children(e.nestedSortCfg.nestingTag + ("." + e.nestedSortCfg.nestingTagClass.split(" ").join("."))).get(0);
				var c = j;
				j += "[" + i + "][children]";
				var d = k(b);
				if (d.length > 0) {
					g[i].children = d;
				}
				j = c;
			});
			return g;
		};
		if (s) {
			if (jQuery.iSort.collected[s]) {
				e = jQuery("#" + s).get(0);
				o[s] = k(e);
			} else {
				for (a in s) {
					if (jQuery.iSort.collected[s[a]]) {
						e = jQuery("#" + s[a]).get(0);
						o[s[a]] = k(e);
					}
				}
			}
		} else {
			for (i in jQuery.iSort.collected) {
				e = jQuery("#" + i).get(0);
				o[i] = k(e);
			}
		}
		return {
			hash: h,
			o: o
		};
	}, findPrecedingItem: function (e) {
		var d = 0;
		var f = jQuery.grep(e.dropCfg.el, function (i) {
			var a = i.pos.y < jQuery.iDrag.dragged.dragCfg.ny && i.pos.y > d;
			if (!a) {
				return false;
			}
			var b;
			if (e.nestedSortCfg.rightToLeft) {
				b = i.pos.x + i.pos.wb + e.nestedSortCfg.snapTolerance > jQuery.iDrag.dragged.dragCfg.nx + jQuery.iDrag.dragged.dragCfg.oC.wb;
			} else {
				b = i.pos.x - e.nestedSortCfg.snapTolerance < jQuery.iDrag.dragged.dragCfg.nx;
			}
			if (!b) {
				return false;
			}
			var c = jQuery.iNestedSortable.isBeingDragged(e, i);
			if (c) {
				return false;
			}
			d = i.pos.y;
			return true;
		});
		if (f.length > 0) {
			return f[f.length - 1];
		} else {
			return null;
		}
	}, isTouchingFirstItem: function (e) {
		var c;
		var d = jQuery.grep(e.dropCfg.el, function (i) {
			var a = c === undefined || i.pos.y < c;
			if (!a) {
				return false;
			}
			var b = jQuery.iNestedSortable.isBeingDragged(e, i);
			if (b) {
				return false;
			}
			c = i.pos.y;
			return true;
		});
		if (d.length > 0) {
			d = d[d.length - 1];
			return d.pos.y < jQuery.iDrag.dragged.dragCfg.ny + jQuery.iDrag.dragged.dragCfg.oC.hb && d.pos.y > jQuery.iDrag.dragged.dragCfg.ny;
		} else {
			return false;
		}
	}, isBeingDragged: function (e, a) {
		var b = jQuery.iDrag.dragged;
		if (!b) {
			return false;
		}
		if (a == b) {
			return true;
		}
		if (jQuery(a).parents("." + e.sortCfg.accept.split(" ").join(".")).filter(function () {
			return this == b;
		}).length !== 0) {
			return true;
		} else {
			return false;
		}
	}, shouldNestItem: function (e, a) {
		if (!a) {
			return false;
		}
		if (e.nestedSortCfg.noNestingClass && jQuery(a).filter("." + e.nestedSortCfg.noNestingClass).get(0) === a) {
			return false;
		}
		if (e.nestedSortCfg.rightToLeft) {
			return a.pos.x + a.pos.wb - (e.nestedSortCfg.nestingPxSpace - e.nestedSortCfg.snapTolerance) > jQuery.iDrag.dragged.dragCfg.nx + jQuery.iDrag.dragged.dragCfg.oC.wb;
		} else {
			return a.pos.x + (e.nestedSortCfg.nestingPxSpace - e.nestedSortCfg.snapTolerance) < jQuery.iDrag.dragged.dragCfg.nx;
		}
	}, nestItem: function (e, a) {
		var b = jQuery(a).children(e.nestedSortCfg.nestingTag + ("." + e.nestedSortCfg.nestingTagClass.split(" ").join(".")));
		var c = jQuery.iSort.helper;
		styleHelper = c.get(0).style;
		styleHelper.width = "auto";
		if (!b.size()) {
			var d = "<" + e.nestedSortCfg.nestingTag + " class='" + e.nestedSortCfg.nestingTagClass + "'></" + e.nestedSortCfg.nestingTag + ">";
			b = jQuery(a).append(d).children(e.nestedSortCfg.nestingTag).css(e.nestedSortCfg.styleToAttach);
		}
		jQuery.iNestedSortable.updateCurrentNestingClass(e, b);
		jQuery.iNestedSortable.beforeHelperRemove(e);
		b.prepend(c.get(0));
		jQuery.iNestedSortable.afterHelperInsert(e);
	}, appendItem: function (e, a) {
		jQuery.iNestedSortable.updateCurrentNestingClass(e, jQuery(a).parent());
		jQuery.iNestedSortable.beforeHelperRemove(e);
		jQuery(a).after(jQuery.iSort.helper.get(0));
		jQuery.iNestedSortable.afterHelperInsert(e);
	}, insertOnTop: function (e) {
		jQuery.iNestedSortable.updateCurrentNestingClass(e, e);
		jQuery.iNestedSortable.beforeHelperRemove(e);
		jQuery(e).prepend(jQuery.iSort.helper.get(0));
		jQuery.iNestedSortable.afterHelperInsert(e);
	}, beforeHelperRemove: function (e) {
		var a = jQuery.iSort.helper.parent(e.nestedSortCfg.nestingTag + ("." + e.nestedSortCfg.nestingTagClass.split(" ").join(".")));
		var b = a.children("." + e.sortCfg.accept.split(" ").join(".") + ":visible").size();
		if (b === 0 && a.get(0) !== e) {
			a.hide();
		}
	}, afterHelperInsert: function (e) {
		var a = jQuery.iSort.helper.parent();
		if (a.get(0) !== e) {
			a.show();
		}
		e.nestedSortCfg.remeasured = false;
	}, updateCurrentNestingClass: function (e, a) {
		var b = jQuery(a);
		if (e.nestedSortCfg.currentNestingClass && (!jQuery.iNestedSortable.currentNesting || b.get(0) != jQuery.iNestedSortable.currentNesting.get(0))) {
			if (jQuery.iNestedSortable.currentNesting) {
				jQuery.iNestedSortable.currentNesting.removeClass(e.nestedSortCfg.currentNestingClass);
			}
			if (b.get(0) != e) {
				jQuery.iNestedSortable.currentNesting = b;
				b.addClass(e.nestedSortCfg.currentNestingClass);
				jQuery.iNestedSortable.latestNestingClass = e.nestedSortCfg.currentNestingClass;
			} else {
				jQuery.iNestedSortable.currentNesting = null;
				jQuery.iNestedSortable.latestNestingClass = "";
			}
		}
	}, destroy: function () {
		return this.each(function () {
			if (this.isNestedSortable) {
				this.nestedSortCfg = null;
				this.isNestedSortable = null;
				jQuery(this).SortableDestroy();
			}
		});
	}, build: function (a) {
		if (a.accept && jQuery.iUtil && jQuery.iDrag && jQuery.iDrop && jQuery.iSort) {
			this.each(function () {
				this.isNestedSortable = true;
				this.nestedSortCfg = {
					noNestingClass: a.noNestingClass ? a.noNestingClass : false,
					rightToLeft: a.rightToLeft ? true : false,
					nestingPxSpace: parseInt(a.nestingPxSpace, 10) || 30,
					currentNestingClass: a.currentNestingClass ? a.currentNestingClass : "",
					nestingLimit: a.nestingLimit ? a.nestingLimit : false,
					autoScroll: a.autoScroll !== undefined ? a.autoScroll == true : true,
					scrollSensitivity: a.scrollSensitivity ? a.scrollSensitivity : 20,
					scrollSpeed: a.scrollSpeed ? a.scrollSpeed : 20,
					serializeRegExp: a.serializeRegExp ? a.serializeRegExp : /[^\-]*$/
				};
				this.nestedSortCfg.snapTolerance = parseInt(this.nestedSortCfg.nestingPxSpace * 0.4, 10);
				this.nestedSortCfg.nestingTag = this.tagName;
				this.nestedSortCfg.nestingTagClass = this.className;
				this.nestedSortCfg.styleToAttach = this.nestedSortCfg.rightToLeft ? {
					'padding-left': 0,
					'padding-right': this.nestedSortCfg.nestingPxSpace + "px"
				} : {
					'padding-left': this.nestedSortCfg.nestingPxSpace + "px",
					'padding-right': 0
				};
				jQuery(this.nestedSortCfg.nestingTag, this).css(this.nestedSortCfg.styleToAttach);
			});
			jQuery.iSort.checkhover = jQuery.iNestedSortable.checkHover;
			jQuery.iSort.check = jQuery.iNestedSortable.check;
			jQuery.iSort.serialize = jQuery.iNestedSortable.serialize;
		}
		return this.Sortable(a);
	}
};
jQuery.fn.extend({
	NestedSortable: jQuery.iNestedSortable.build,
	NestedSortableDestroy: jQuery.iNestedSortable.destroy
});
jQuery.iUtil.getScroll = function (e) {
	var t, l, w, h, iw, ih;
	if (e && e.nodeName.toLowerCase() != "body") {
		t = e.scrollTop;
		l = e.scrollLeft;
		w = e.scrollWidth;
		h = e.scrollHeight;
		iw = 0;
		ih = 0;
	} else {
		if (document.documentElement && document.documentElement.scrollTop) {
			t = document.documentElement.scrollTop;
			l = document.documentElement.scrollLeft;
			w = document.documentElement.scrollWidth;
			h = document.documentElement.scrollHeight;
		} else if (document.body) {
			t = document.body.scrollTop;
			l = document.body.scrollLeft;
			w = document.body.scrollWidth;
			h = document.body.scrollHeight;
		}
		iw = self.innerWidth || document.documentElement.clientWidth || document.body.clientWidth || 0;
		ih = self.innerHeight || document.documentElement.clientHeight || document.body.clientHeight || 0;
	}
	return {
		t: t,
		l: l,
		w: w,
		h: h,
		iw: iw,
		ih: ih
	};
};