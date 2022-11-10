var config = {
	"map": {
		"*": {
			"mobileapp/select2": "Drc_Mobileapp/js/select2.full",
			"mobileapp/selectBoxIt": "Drc_Mobileapp/js/selectBoxIt.min",
		}
	},

	"paths": {  
		"mobileapp/select2": "Drc_Mobileapp/js/select2.full",
		"mobileapp/selectBoxIt": "Drc_Mobileapp/js/selectBoxIt.min",
	},   
    "shim": {
		"Drc_Mobileapp/js/select2.full": ["jquery"],
		"Drc_Mobileapp/js/selectBoxIt.min": ["jquery"]
	}
};