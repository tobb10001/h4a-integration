# H4a-Integration

This is a small library to integrate [Handball4All](handball4all.de) results and
tables into a PHP website, that has some kind of persistence to it.

## Concept

Calling H4A on every of your clients request has two major downsides:

- the nested request _massively_ slows down the original request
- the effect amplificates, if it is desired to deliver content, that itself
cannot be requested from H4A in a single request

Therefore, this library is meant to scrape all needed information into a local
database (or any kind of persistence), such that this can feed the resulting
page with a maximum of performance and flexibility.

## How it's done

The important components to understand are the `Updater`, the
`PersistenceInterface`, and the Models.\
You will need to provide a `PersistenceInterface` yourself, to actually fit your
persistence solution. You can take a look at
[`SqliteAdapter`](src/Persistence/SqliteAdapter.php) to see a sample
implementation, or just use it if it fits your needs.

The `Updater` is the central component. It proviedes the update function, which
gathers all information for all teams and stores it.

For it to work, it needs an object, that implements the `PersistenceInterface`.
It's responsibilities are to

- tell the `Updater`, which teams need to be updated, via the `getTeams` method
- save the data gahtered by the `Updater` using the `replaceLeagueData` method

The Models are how those components communicate with each other and also how you
would probably want to access the gathered data afterwards.\
Those models might seem confusing at first glance, but they try to resemble the
H4A API-Response as close as possible. Therefore, you can look at the H4A page
to actually see, what all those fields refere to.\
There are:

- `Game`: One single Game
- `GameSchedule`: A set of games for the associated team, as well as the
league's metadata
- `LeagueData`: An triplet joining `LeagueMetadata`, a `Table` and a
`GameSchedule`
- `LeagueMetadata`: Metadata, that belongs to a league
- `TabScore`: A line in the league's table
- `Table`: A Table, no more than a collection of `TabScore`s
- `Team`: A Team.
    In contrast to all previous models the `Team` has no relation to any API
    response. It is only used to communicate between the `PersistenceInterface`
    and the `Updater`.

## Example

```php

use H4aIntegration\Tobb10001\Updater;

/**
 * Updating Function.
 * You would probably want to hook this function into some kind of cron mechanism.
 */
function updateH4a() {
    persistence = new MyPersistence(...);  // must implement PersistenceInterface
    updater = new Updater(persistence);

    result = updater->update();
    // do logging or whatever with result
}
```
