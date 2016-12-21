# SkelPHP Config

*NOTE: The Skel framework is an __experimental__ web applications framework that I've created as an exercise in various systems design concepts. While I do intend to use it regularly on personal projects, it was not necessarily intended to be a "production" framework, since I don't ever plan on providing extensive technical support (though I do plan on providing extensive documentation). It should be considered a thought experiment and it should be used at your own risk. Read more about its conceptual foundations at [my website](https://colors.kaelshipman.me/about/this-website).*

Configuration is an interesting issue to me. Many components need configuration, and they're not necessarily all related. You need to provide default configurations that can be overridden on a per-install basis, and ideally all of your configurations would be in one place.

This Config class is designed to fulfill these requirements. The trick is that you specify which configurations you'll need via interfaces. For example, a `Db` assumes there's a Content directory on the filesystem and some sort of a PDO that serves as the basis for data transactions. Thus, the `DbConfig` interface defines the methods `getDbPdo` and `getDbContentRoot`. If you pass a config object to a `Db` constructor that doesn't implement `DbConfig`, it'll throw an error. Furthermore, if you implement `DbConfig`, but don't provide the configuration variables it's looking for in either of your configuration files (global or local), it'll throw an error in beta (though will be silently dissatisfied with you in production).

I'm not sure how I feel about all this right now. My initial thoughts are positive, but it could get extremely cumbersome in a complex app. That said, there's no major consequence for simply adding configuration variables that aren't defined. In such a case, you'd have free access to them (via `Config::get`), but you wouldn't be alerted if they were somehow inadvertently not set, which opens up the possibility of embarrassing and unexpected program glitches in production.

## Installation

Eventually, this package is intended to be loaded as a composer package. For now, though, because this is still in very active development, I currently use it via a git submodule:

```bash
cd ~/my-website
git submodule add git@github.com:kael-shipman/skelphp-config.git app/dev-src/skelphp/config
```

This allows me to develop it together with the website I'm building with it. For more on the (somewhat awkward and complex) concept of git submodules, see [this page](https://git-scm.com/book/en/v2/Git-Tools-Submodules).

