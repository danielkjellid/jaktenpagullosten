#!/bin/bash
# add " at beginning
sed -e 's/^/\"/' data.txt > edited_data.txt
# add T-bane]"
awk '{print $0, "[T-bane]\""}' edited_data.txt > fulldata.txt
