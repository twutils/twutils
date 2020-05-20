import get from 'lodash/get'
import snakeCase from 'lodash/snakeCase'

const __ = (key) => {
  if (!key) { return null }

  const translation = get(window.TwUtils.langStore, key)

  if (!translation) { return snakeCase(key).replace(new RegExp(`_`, `g`), ` `) }

  return translation
}

export default __
