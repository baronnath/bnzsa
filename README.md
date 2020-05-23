# bnzsa

## Notes
- All matches are played by the customer team.
- API provides game id and team id.
- The entity event is clasified by type, stored as event_type table: goals, penalties, card, etc
- Request is converted to json by the json-request-bundle
- The results are stored as string in the following format: '<our_client_score>:<adversary_score>'

## JSON request sample
```
// Type 1
{
	"matches": [
		{
			"matchId": ,
			"place": ,
			"datetime": ,
			"teamId": ,
			"results": [2, 0], // adversary score is second place in array
			"ended": false
		}
	]
}

// Type 2
{
	"matches": [
		{
			"matchId": ,
			"place": ,
			"datetime": ,
			"teamId": ,
			"results": [2, 0],  // adversary score is second place in array
			"ended": false,
			"players": [ , , ],
			"events": {
				{
					"playerId": ,
					"type": ,
					"datetime": 
				},
				{
					"playerId": ,
					"type": ,
					"datetime": 
				}
			}
		}
	]
}
```
