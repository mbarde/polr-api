<a name="top"></a>
# Polr API v2.0.0

Restful API for the Polr URL Shortener

- [Links](#links)
  - [Delete a link](#delete-a-link)
  - [Get Admin Links](#get-admin-links)
  - [Get User Links](#get-user-links)
  - [Lookup Link](#lookup-link)
  - [Shorten a link](#shorten-a-link)
  - [Update a link](#update-a-link)
  
- [Stats](#stats)
  - [Get Link Stats](#get-link-stats)
  - [Get Stats](#get-stats)
  
- [Users](#users)
  - [Change API Settings](#change-api-settings)
  - [Generate Key](#generate-key)
  - [Get a User](#get-a-user)
  - [Get Users](#get-users)
  - [Update a user](#update-a-user)
  


# Links

## Delete a link
[Back to top](#top)

<p>Delete the link with the given ending.</p>

  DELETE /links/:ending





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to delete links.</p>|
### Error 404

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| NotFound | Object | <p>Unable to find a link with the given ending.</p>|


## Get Admin Links
[Back to top](#top)

<p>Fetch a paginated list of links. The input parameters are those of the Datatables library.</p>

  GET /links





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| draw | Integer | **optional**<p>The draw option.</p>|
| columns | Object | **optional**<p>The table columns.</p>|
| order | Object | **optional**<p>The data ordering.</p>|
| start | Integer | **optional**<p>The data offset.</p>|
| length | Integer | **optional**<p>The data count.</p>|
| search | Object | **optional**<p>The search options.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Object | <p>The link list.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to list links.</p>|


## Get User Links
[Back to top](#top)

<p>Fetch a paginated list of links. The input parameters are those of the Datatables library.</p>

  GET /user/links





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| draw | Integer | **optional**<p>The draw option.</p>|
| columns | Object | **optional**<p>The table columns.</p>|
| order | Object | **optional**<p>The data ordering.</p>|
| start | Integer | **optional**<p>The data offset.</p>|
| length | Integer | **optional**<p>The data count.</p>|
| search | Object | **optional**<p>The search options.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Mixed | <p>The link list.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to list links.</p>|


## Lookup Link
[Back to top](#top)

<p>Returns</p>

  GET /links/:ending





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|
| secret | String | **optional**<p>The link secret.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Object | <p>The link data.</p>|


### Error 404

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| NotFound | Object | <p>Unable to find a link with the given ending.</p>|
### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>Invalid URL code given for a secret URL.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


## Shorten a link
[Back to top](#top)

<p>Create a shortened URL for a given link</p>

  POST /links





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|
| url | String | <p>The link to shorten.</p>|
| ending | String | **optional**<p>A custom ending for the link.</p>|
| secret | String | **optional**<p>Create a secret link or not.</p>|
| ip | String | **optional**<p>The IP address the request came from.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Mixed | <p>The shortened URL.</p>|


### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| CreationError | Object | <p>An error occurs while shortening the link.</p>|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


## Update a link
[Back to top](#top)

<p>Update the link with the given ending.</p>

  PUT /links/:ending





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|
| url | String | **optional**<p>The new URL.</p>|
| status | String | **optional**<p>The status change: enable, disable or toggle.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to edit the link.</p>|
### Error 404

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| NotFound | Object | <p>Unable to find a link with the given ending.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


# Stats

## Get Link Stats
[Back to top](#top)

<p>Fetch stats of a given type for a single link.</p>

  GET /links/:ending/stats





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|
| type | String | <p>The type of stats to fetch.</p>|
| ending | String | <p>The short URL id of the link.</p>|
| left_bound | String | <p>The start date.</p>|
| right_bound | String | <p>The end date.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Mixed | <p>The stats data.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to view stats.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AnalyticsError | Object | <p>An error occurs while fetching stats from the database.</p>|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


## Get Stats
[Back to top](#top)

<p>Fetch stats of a given type.</p>

  GET /stats





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|
| type | String | <p>The type of stats to fetch.</p>|
| left_bound | String | <p>The start date.</p>|
| right_bound | String | <p>The end date.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Mixed | <p>The stats data.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to view stats.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AnalyticsError | Object | <p>An error occurs while fetching stats from the database.</p>|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


# Users

## Change API Settings
[Back to top](#top)

<p>Change the API Settings of the user with the given id.</p>

  PUT /users/:id/api





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|
| quota | String | **optional**<p>The new quota.</p>|
| status | String | **optional**<p>The access change: enable, disable or toggle.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Object | <p>The updated user data.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to edit the user.</p>|
### Error 404

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| NotFound | Object | <p>Unable to find a user with the given id.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


## Generate Key
[Back to top](#top)

<p>Generate a new API access key for the user with the given id.</p>

  POST /users/:id/api





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Mixed | <p>The updated user data.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to edit the user.</p>|
### Error 404

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| NotFound | Object | <p>Unable to find a user with the given id.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


## Get a User
[Back to top](#top)

<p>Get the user with the given id</p>

  GET /users/:id





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Object | <p>The user data.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to get users.</p>|
### Error 404

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| NotFound | Object | <p>Unable to find a user with the given id.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


## Get Users
[Back to top](#top)

<p>Fetch a paginated list of users. The input parameters are those of the Datatables library.</p>

  GET /users





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| draw | Integer | **optional**<p>The draw option.</p>|
| columns | Object | **optional**<p>The table columns.</p>|
| order | Object | **optional**<p>The data ordering.</p>|
| start | Integer | **optional**<p>The data offset.</p>|
| length | Integer | **optional**<p>The data count.</p>|
| search | Object | **optional**<p>The search options.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Object | <p>The user list.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to list users.</p>|


## Update a user
[Back to top](#top)

<p>Update the user with the given id.</p>

  PUT /users/:id





### Parameter Parameters

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| key | String | <p>The user API key.</p>|
| role | String | **optional**<p>The new role.</p>|
| status | String | **optional**<p>The user status change: enable, disable or toggle.</p>|


### Success 200

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| message | String | <p>The response message.</p>|
| settings | Object | <p>The Polr instance config options.</p>|
| result | Object | <p>The updated user data.</p>|


### Error 401

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| AccessDenied | Object | <p>The user does not have permission to edit the user.</p>|
### Error 404

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| NotFound | Object | <p>Unable to find a user with the given id.</p>|
### Error 400

| Name     | Type       | Description                           |
|:---------|:-----------|:--------------------------------------|
| MissingParameters | Object | <p>There is a missing or invalid parameter.</p>|


