# Privacy

<br/>

## 1. TwUtils permissions on your Twitter account

A TwUtils account can only be created by logging in with Twitter. This means that you give TwUtils some of the permissions shown below to control your Twitter account.

Instead of directly requesting Twitter for the highest permissions (write & delete permissions), TwUtils was designed so that it only asks for the minmum required permissions on login, which is: Read-Only permissions. So that the user has the option to link higher permissions when he wants to perform tasks related to those permissions.

### 1.1 Read-Only Permissions

While requesting read-only permissions, Twitter shows an explanation stating some -but not all- of the permissions that will be granted to the application that will request the permissions, in our case this application is TwUtils. Among these permissions are:

- Read your tweets and the tweets of your followings from your account -even if the account is private -.
- See your followings & followers lists.
- See the email associated with Twitter.

The tasks associated with these permissions in tweets, are the copying tasks only:

- Backup likes
- Backup tweets
- Backup followers list
- Backup followings list

### 1.2 Write Permissions

It is called "Write Permissions" with this name because it gives a higher level of control than just reading the information from your account. But it doesn't mean that it's only for "Writing" related operations. The write permission will grant the application the permissions will to write tweets, delete them, modify your profile, and follow or cancel followers. TwUtils never write tweets or update your profile.

Among all these permissions, TwUtils requires only the ability to delete tweets and remove likes. Twitter API wasn't designed to allow for a selective and custom permissions. Thus, TwUtils has no way to perform the "remove tweets or likes" operations without an access to this whole unused permissions.

While requesting write permissions, Twitter shows an explanation stating this permissions:

- Read your tweets and the tweets of your followings from your account -even if the account is private -.
- See your followings & followers lists.
- See the email associated with Twitter.
- Update your profile
- Post tweets from your account

But the tasks associated with write permissions in TwUtils are limited only to:

- Delete likes
- Delete tweets

### 1.3 Canceling Twitter permissions on your Twitter account

Twitter allows you to control the applications that have permissions to your Twitter account via this page:
[Settings -> Applications] (https://twitter.com/settings/applications)

-----

## 2. Manage your TwUtils account

You can go to the [Profile page](/profile) to control your TwUtils account.

### 2.1 See account information

The profile page allows you to check basic account information (your Twitter email, your linked Twitter account, last login and account creation date).

### 2.2 Canceling TwUtils' access to your Twitter Account, via TwUtils

In the profile page, you can revoke TwUtils' access to your account.

Under the "Twitter Access" section, There is a table shows how TwUtils access your Twitter account and the permission associated with each access.

You can add the required permissions (Read Permissions, Write Permissions) via the "Add" button.

For added permissions, you can revoke them via the "Revoke Access" button.

In case "Read Permissions" was revoked, it will be requested again upon logging in the next time after logging out.

### 2.3 Delete the entire account

The profile page provides the ability to delete your entire account from TwUtils.

Deleting an account from TwUtils does not mean deleting or disabling your Twitter account.

When you delete the account, all the content and activities that you did on TwUtils will be deleted as well, you will not be able to undo this action, and when you log in again the account will be treated as a new account without any connection to the old account.

When the account is deleted, it will also:

- Delete the main user account, email and activity information
- Delete all permissions to your twitter account
- Delete all tasks associated with the account

When you delete your account from TwUtils, TwUtils will also delete the tweets, users information that retrieved via tasks created from your account.