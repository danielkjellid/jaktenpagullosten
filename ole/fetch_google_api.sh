#!/bin/bash
#
# Google Maps Places Web Service
#
# Place Search
# Place Details
# Place Photos
# Place IDs
# Place Types


# variables / constants
KEY=
SEARCH="nearbysearch"
RADIUS=1000
BASE_URL="https://maps.googleapis.com/maps/api/place" 
RESULT_TYPE="json"
LOCATION="-33.8670522,151.1957362"
SEARCH_TYPE="resturant|zoo|bar|cafe|museum|night_club|gym"
RANK_BY="prominence"
# rankby: prominence / distance / location
# curl "$BASE_URL/$SEARCH_TYPE/$RESULT_TYPE/?location=$LOCATION&radius=$RADIUS&type=$SEARCH_TYPE&key=$KEY"

function searchLatLong() {
	# https://maps.googleapis.com/maps/api/place/nearbysearch/output?parameters
	echo "$BASE_URL/$SEARCH/$RESULT_TYPE?location=$1&radius=$RADIUS&type=$SEARCH_TYPE&key=$KEY&region=NO&language=NO&rankby=$RANK_BY"
}

function getLatLongFromName() {
}

function parseLatLongFile() {
EXT_FILE="$1"
while IFS= read -r place
do
searchLatLong $place
done <"$EXT_FILE"
}

while test $# -gt 0; do
    case "$1" in -h|--help)
			echo "------------------------------------"
		        echo "$package - Google Maps Places API using Web Services"
    			echo " "
                        echo "$package [options]"
                        echo " "
                        echo "options:"
                        echo "--help            What you are looking at.."
                        echo "-f, --file        file with location"
                        echo "-s, --search      search for something"
                        echo "-d, --details 	fetching details"
				echo "------------------------------------"
                        exit 0
                        ;;
                -s|--search)
                        
                     	PARAMETER="$2"   
                        echo "Google Places text search for..\"$PARAMETER\" "
                        echo " "
						echo "URL: $URL"
						exit 1;
                        
                        shift
                        ;;
                -d|--details)
						
						PARAMETER="$2"
                        echo "Google Places details search for \"$PARAMETER\""
                        echo " "
                        echo "URL: $URL"
                        exit 1;
						
						shift
                        ;;

                -f|--file)
                        
						file="$2"
                        echo "Google Places details using file: \"$file\""
                        echo " "
                        parseLatLongFile $file
                        exit 1;
						
						shift
                        ;;
                *)
			break
                        ;;
        esac
done
