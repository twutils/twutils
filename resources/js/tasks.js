
/**

--- Dashboard

- My Tweets

-- Copy my tweets

-- Delete my tweets

- My Likes

-- Copy my likes

-- Delete my tweets

- My Following

-- Copy my following list

-- Copy my followers list

-- Monitor my following list for changes

*/

export default [
  {
    category: `explore`,
    invisible: true,
    scope: `read`,
    type: undefined,
    route: { path: `/`, },
    langButton: `dashboard`,
  },
  {
    category: `tweets`,
    scope: `read`,
    type: `userTweets`,
    route: { name: `task.add`, params: { type: `userTweets`, }, },
    langButton: `user_tweets`,
    icon: `fa fa-save`,
  },
  {
    category: `tweets`,
    scope: `write`,
    type: `destroyTweets`,
    route: { name: `task.add`, params: { type: `destroyTweets`, }, },
    langButton: `destroy_tweets`,
    icon: `fa fa-trash-o`,
  },
  {
    category: `likes`,
    scope: `read`,
    type: `backupLikes`,
    route: { name: `task.add`, params: { type: `backupLikes`, }, },
    langButton: `backup_likes`,
    icon: `fa fa-save`,
  },
  {
    category: `likes`,
    scope: `write`,
    type: `destroyLikes`,
    route: { name: `task.add`, params: { type: `destroyLikes`, }, },
    langButton: `destroy_likes`,
    icon: `fa fa-trash-o`,
  },
  {
    category: `following`,
    scope: `read`,
    type: `fetchFollowing`,
    route: { name: `task.add`, params: { type: `fetchFollowing`, }, },
    langButton: `fetch_following`,
    icon: `fa fa-save`,
  },
  {
    category: `followers`,
    scope: `read`,
    type: `fetchFollowers`,
    route: { name: `task.add`, params: { type: `fetchFollowers`, }, },
    langButton: `fetch_followers`,
    icon: `fa fa-save`,
  },
]
