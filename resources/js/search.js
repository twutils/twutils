/*
 * Parts of this file are stripped off from "js/application.js" in
 * the official Twitter Archives..
 *
 */

import get from 'lodash/get'

function escapeRegexCharacters (text) {
  return text.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, `\\$&`)
}

function escapeURL (text) {
  return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, `\\$&`)
}

function unescapeHtml (text) {
  var div = document.createElement(`div`)
  div.innerHTML = text
  return div.firstChild.nodeValue
}

const searchFunc = function (tweet, searchRegex) {
  var searchMatch = false
  if (unescapeHtml(tweet.text).match(searchRegex)) {
    searchMatch = true
  } else if (tweet.tweep.name && (tweet.tweep.screen_name.match(searchRegex) || (`@` + tweet.tweep.screen_name).match(searchRegex) || tweet.tweep.name.match(searchRegex))) {
    searchMatch = true
  } else if (get(tweet, `retweeted_status.user.name`) && get(tweet, `retweeted_status.user.name`).match(searchRegex)) {
    searchMatch = true
  } else if (get(tweet, `retweeted_status.user.screen_name`) && get(tweet, `retweeted_status.user.screen_name`).match(searchRegex)) {
    searchMatch = true
  } else if (get(tweet, `quoted_status.user.name`) && get(tweet, `quoted_status.user.name`).match(searchRegex)) {
    searchMatch = true
  } else if (get(tweet, `quoted_status.user.screen_name`) && get(tweet, `quoted_status.user.screen_name`).match(searchRegex)) {
    searchMatch = true
  } else if (get(tweet, `quoted_status.full_text`) && get(tweet, `quoted_status.full_text`).match(searchRegex)) {
    searchMatch = true
  }
  return searchMatch
}

const searchFields = function (obj, search, fields) {
  const searchRegex = new RegExp(escapeRegexCharacters(search), `im`)
  var searchMatch = false

  fields.map(field => {
    if (searchMatch) { return }

    if (get(obj, field) && get(obj, field).match(searchRegex)) { searchMatch = true }
  })

  return searchMatch
}

const searchArrayByFields = function (array, search, fields) {
  const filteredResults = array.filter(item => searchFields(item, search, fields))

  return filteredResults
}

const searchTweets = (tweets, search) => {
  const searchRegex = new RegExp(escapeRegexCharacters(search), `im`)
  var filteredResults = tweets.filter(tweet => searchFunc(tweet, searchRegex))

  return filteredResults
}

export { searchArrayByFields }

export default searchTweets
