<?php

//CONFIGURATION for SmartAdmin UI

//ribbon breadcrumbs config
//array("Display Name" => "URL");
$breadcrumbs = array(
	"Home" => APP_URL
);

/*navigation array config

ex:
"dashboard" => array(
	"title" => "Display Title",
	"url" => "http://yoururl.com",
	"url_target" => "_self",
	"icon" => "fa-home",
	"label_htm" => "<span>Add your custom label/badge html here</span>",
	"sub" => array() //contains array of sub items with the same format as the parent
)

*/
$page_nav = array(
	"dashboard" => array(
		"title" => "Dashboard",
		"url" => APP_URL,
		"icon" => "fa-dashboard"
	),
	"enquiry" => array(
		"title" => "Enquiries",
		"url" => APP_URL.'/booking/enquiry',
		"icon" => "fa-search"
	),
	"party" => array(
			"title" => "Parties",
			"icon" => "fa-users",
			"sub" => array(
					"new" => array(
							"title" => "Create New",
							"url" => APP_URL.'/party/newRecord'
					),
					"existing" => array(
							"title" => "Manage Existing",
							"url" => APP_URL.'/party'
					)
			)
	),
	"agent" => array(
			"title" => "Agents",
			"icon" => "fa-bullhorn",
			"sub" => array(
					"new" => array(
							"title" => "Create New",
							"url" => APP_URL.'/agent/newRecord'
					),
					"existing" => array(
							"title" => "Manage Existing",
							"url" => APP_URL.'/agent'
					)
			)
	),
	"booking" => array(
		"title" => "Bookings",
		"icon" => "fa-book",
		"sub" => array(
			"new" => array(
				"title" => "Create New",
				"url" => APP_URL.'/booking/newRecord'
			),
			"existing" => array(
				"title" => "Manage Existing",
				"url" => APP_URL.'/booking/'
			)
		)
	),
	"quote" => array(
		"title" => "Quotes",
		"icon" => "fa-file-text-o",
		"sub" => array(
			"new" => array(
				"title" => "Create New",
				"url" => APP_URL.'/quote/newRecord'
			),
			"existing" => array(
				"title" => "Manage Existing",
				"url" => APP_URL.'/quote'
			)
		)
	),
	"payment" => array(
		"title" => "Payments",
		"icon" => "fa-money",
		"sub" => array(
			"new" => array(
				"title" => "Create New",
				"url" => APP_URL.'/payment_detail/newRecord'
			),
			"existing" => array(
				"title" => "Manage Existing",
				"url" => APP_URL.'/payment_detail'
			)
		)
	),
	"reports" => array(
		"title" => "Reports",
		"icon" => "fa-th-list",
		"sub" => array(
			"booking" => array(
				"title" => "Booking Sheet",
				"url" => APP_URL.'/booking/bookingSheet'
			),
			"availability" => array(
				"title" => "Availability",
				"url" => APP_URL.'/booking/availabilityChart'
			),
			"rooming" => array(
					"title" => "Rooming",
					"url" => APP_URL.'/booking/roomingChart'
			),
			"bednight" => array(
					"title" => "Bed Nights",
					"url" => APP_URL.'/booking/bednightsChart'
			),
			"sales" => array(
					"title" => "Sales Report",
					"url" => APP_URL.'/booking/salesChart'
			),
			"agentc" => array(
					"title" => "Agent Commissions",
					"url" => APP_URL.'/booking/agentChart'
			),
			"topclients" => array(
					"title" => "Top Clients",
					"url" => APP_URL.'/booking/topClients'
			)
				
				
		)
	),
	"admin" => array(
		"title" => "Administration",
		"icon" => "fa-gear",
		"sub" => array(
			"user" => array(
				"title" => "Users",
				"url" => APP_URL.'/admin/userTable'
			),
			"agent" => array(
				"title" => "Agents",
				"url" => APP_URL.'/admin/agentTable'
			),
			"providers" => array(
				"title" => "Service Providers",
				"url" => APP_URL.'/admin/service_providerTable'
			),
			"property" => array(
				"title" => "Properties",
				"url" => APP_URL.'/admin/propertyTable'
			),
			"propertyrooms" => array(
				"title" => "Property Rooms",
				"url" => APP_URL.'/admin/property_roomsTable'
			),
			"paymethod" => array(
				"title" => "Payment Methods",
				"url" => APP_URL.'/admin/payment_methodTable'
			),
			"roomtype" => array(
				"title" => "Room Types",
				"url" => APP_URL.'/admin/room_typeTable'
			),
			"booktype" => array(
				"title" => "Booking Types",
				"url" => APP_URL.'/admin/booking_typeTable'
			),
			"transtype" => array(
				"title" => "Transport Types",
				"url" => APP_URL.'/admin/transport_typeTable'
			),
			"season" => array(
				"title" => "Seasons",
				"url" => APP_URL.'/admin/seasonsTable'
			),
			"season_dates" => array(
				"title" => "Season Dates",
				"url" => APP_URL.'/admin/season_datesTable'
			),
			"rack_rates" => array(
				"title" => "Rack Rates",
				"url" => APP_URL.'/admin/rack_ratesTable'
			),
			"qbacc" => array(
						"title" => "Quickbooks Payment A/Cs",
						"url" => APP_URL.'/admin/quickbooks_accountsTable'
			),
			"param" => array(
				"title" => "Parameter",
				"url" => APP_URL.'/admin/parameterTable'
			),
			"park" => array(
					"title" => "Park Fees",
					"url" => APP_URL.'/admin/park_feesTable'
			),
		        "rttype" => array(
					"title" => "Reservation Types",
					"url" => APP_URL.'/admin/reservation_typeTable'
			)
		)
	)
);

//configuration variables
$page_title = "";
$page_css = array();
$no_main_header = false; //set true for lock.php and login.php
$page_body_prop = array(); //optional properties for <body>
$page_html_prop = array(); //optional properties for <html>
?>
