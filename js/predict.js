function predictVM() {
	var self = this;

	this.initialized = ko.observable(false);
	this.results = ko.observableArray();
	this.schools = ko.observableArray();
	this.schoolType = ko.observable('3');

	this.schoolsKeyWord = ko.observable('');

	this.displayMode = ko.observable('detail');

	this.showTyp = function() {
		$('#sel-typ').dialog('open');
	};

	this.showSch = function() {
		$('#sel-sch').dialog('open');
	};

	this.showBri = function() {
		self.displayMode('brief');
	};

	this.showDet = function() {
		self.displayMode('detail');
	};

	this.mailStatus = ko.observable('');
	var jqMail = $('#mailing');
	this.sendMail = function() {
		self.mailStatus('傳送中');
		jqMail.dialog( 'option', 'buttons', {} );
		jqMail.dialog( 'open' );
		$.get('mail.php', function( data ) {
			self.mailStatus('傳送成功');
			jqMail.dialog( 'option', 'buttons', {
				'確認': function() {
					$(this).dialog('close');
				}
			});
		});
	}

	$.get( 'init.php', function( data ) {
		var smap = {}, cmap = {}, schoolsList = [];
		if( !data.schoolsSelected ) {
			smap = '';
		} else {
			$.each( data.schoolsSelected.split(','), function(){
				smap[this] = true;
			});
		}
		$.each( data.schools, function( sid, name) {
			var checked = ko.observable( !smap || sid in smap );
			schoolsList.push( { name: name, sid: sid, checked: checked, hide: ko.observable(false) } );
		});
		self.schools( schoolsList );
	});

	var checkedChanged = ko.computed( function() {
		var ret = '';
		$.each( self.schools(), function() {
			ret += (this.checked()-0);
		});
		return ret;
	});

	var schoolFilter = ko.computed( function() {
		var key = $.trim(self.schoolsKeyWord()),
			ret = '';

		if( !key ) {
			$.each( self.schools(), function() {
				this.hide(false);
				ret += '0';
			});
		} else {
			$.each( self.schools(), function(i,data) {
				var match = data.name.match(key) === null;
				ret += (match-0);
				data.hide( match );
			});
		}
		return ret;
	});

	var changedCounter = 0;
	var debouncer = function() {
		if( changedCounter >= 0 ) {
			setTimeout( function() {
				if( changedCounter > 0 ) {
					changedCounter = 0;
					debouncer();
				} else if( changedCounter == 0 ) {
					changedCounter = -1;
					updateResult();
				}
			}, 400);
		}
	}
	var sthChanged = ko.computed( function() {

		checkedChanged();
		schoolFilter();
		self.schoolType();

		changedCounter = 1;
		debouncer();

	});

	var updateResult = function() {
		var targetSchools = [],
			targetClasses = [];

		$.each( self.schools(), function() {
			if( this.checked() && !this.hide() )
				targetSchools.push( this.sid );
		});
		$.ajax({
			url: 'predict.php',
			data: {
				schools: targetSchools.join(','),
				classes: targetClasses.join(','),
				schoolType: self.schoolType()
			},
			success: function( data ) {
				self.initialized(true);
				if( data.results ) {
					self.results( data.results );
				}
			},
			dataType: 'json',
			cache: true
		});
	}
}

$(function() {

	ko.applyBindings( new predictVM() );

	var diaOpt = {
		autoOpen: false,
		modal: true,
		draggable: false,
		resizable: false,
		width: 300,
		height: $(window).height() - 30,
		buttons: {
			'確認': function() {
				$(this).dialog('close');
			}
		}
	};
	$('#sel-typ').dialog(diaOpt);
	$('#sel-sch').dialog(diaOpt);
	$('#mailing').dialog( $.extend({}, diaOpt, { height: 200, buttons: {} }) );

});
