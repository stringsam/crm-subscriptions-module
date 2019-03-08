# CRM Subscriptions Module

This documentation describes API handlers provided by this module for
others to use. It expects your application is based on the [CRM skeleton](https://github.com/remp2020/crm-skeleton)
provided by us.

## Installing module

We recommend using Composer for installation and update management.

```shell
composer require remp/crm-subscriptions-module
```

## Enabling module

Add installed extension to your `app/config/config.neon` file.

```neon
extensions:
	- Crm\SubscriptionsModule\DI\SubscriptionsModuleExtension
```

## API documentation

All examples use `http://crm.press` as a base domain. Please change the host to the one you use
before executing the examples.

All examples use `XXX` as a default value for authorization token, please replace it with the
real tokens:

* TODO: how to get API tokens
* TODO: how to get User tokens

API responses can contain following HTTP codes:

| Value | Description |
| --- | --- |
| 200 OK | Successful response, default value | 
| 400 Bad Request | Invalid request (missing required parameters) | 
| 403 Forbidden | The authorization failed (provided token was not valid) | 
| 404 Not found | Referenced resource wasn't found | 

If possible, the response includes `application/json` encoded payload with message explaining
the error further.

---

#### GET `/api/v1/users/subscriptions`

Returns all user subscriptions with information when they start/end and which content type they give an access to.

##### *Headers:*

| Name | Value | Required | Description |
| --- | --- | --- | --- |
| Authorization | Bearer *String* | yes | User token. |

##### *Params:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| show_finished | *Boolean* | no | Flag indicating whether to include finished subscriptions or not. If not provided, only active and future subscriptions are returned.  |


##### *Example:*

```shell
curl -X POST \
  http://crm.press/api/v1/users/subscriptions \
  -H 'Authorization: Bearer XXX' \
  -H 'Content-Type: application/x-www-form-urlencoded'
```

Response:

```json5
{
    "status": "ok",
    "subscriptions": [ // Array; list of subscriptions
        {
            "start_at": "2019-01-15T00:00:00+01:00", // String; RFC3339 encoded start time
            "end_at": "2020-01-15T00:00:00+01:00", // String; RFC3339 encoded end time
            "type": "time_archive", // String; type of subscription, available values in ["time", "time_archive"]
            "code": "web_year", // String; subscription code (slug)
            "access": [ // Array: list of all types of content subscription includes
                "web" // String; name of the content type
            ]
        },
        {
            "start_at": "2019-03-05T00:00:00+01:00",
            "end_at": "2019-03-19T00:00:00+01:00",
            "type": "time_archive",
            "code": "mobile-welcome-action",
            "access": [
                "web",
                "mobile"
            ]
        }
    ]
}
```

---

#### POST `/api/v1/subscriptions/create`

Creates new subscription for given user and returns new instance.

##### *Headers:*

| Name | Value | Required | Description |
| --- | --- | --- | --- |
| Authorization | Bearer *String* | yes | Bearer token. |

##### *Params:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| email | *String* | yes | Email of existing user. |
| subscription_type_id | *String* | yes | ID of subscription type to use. List of all subscription types is available at `/subscriptions/subscription-types-admin`. |
| start_time | *String* | no | RFC3339 formatted start time. If not present, subscription will start immediately. |
| type | *String* | yes | Type of subscription - values allowed: `regular`, `free`, `donation`, `gift`, `special`, `upgrade`, `prepaid`. If not provided, defaults to `regular`. |


##### *Example:*

```shell
curl -X POST \
  http://crm.press/api/v1/subscriptions/create \
  -H 'Authorization: Bearer XXX' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'email=user%40user.sk&subscription_type_id=73'
```

Response:

```json5
{
    "status": "ok",
    "message": "Subscription created",
    "subscriptions": {
        "id": 628893, // string; ID of new subscription 
        "start_time": "2019-03-08T13:35:05+01:00", // String; RFC3339 formatted start time of subscription
        "end_time": "2019-05-09T13:35:05+02:00" // String; RFC3339 formatted end time of subscription
    }
}
```