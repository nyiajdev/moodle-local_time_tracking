FEATURES

* Track user activity on all pages
* Warns and logs out users after inactivity
* Track guest usage
* Stores user IP addresses
* Privacy support

## Track only *some* users

The `local/time_tracking:trackactivity` capability is given to all roles by default. Simply disallow this capability to roles you do not wish to track.

> Note: site administrators are never tracked. 

## Capabilities

Name | Description | Default allow
------------ | ------------- | -------------
local/time_tracking:viewreports | View Time Tracking reports | Managers
local/time_tracking:trackactivity | Track this user\'s activity and time spent | All
