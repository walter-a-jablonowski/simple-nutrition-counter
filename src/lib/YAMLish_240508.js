class YAMLish
{
  static parse(yamlLike) {
    return JSON.parse(
      yamlLike.replace(/(\w+):/g, '"$1":')
    )
  }

  static dump(obj) {
    return JSON.stringify(obj)
      .replace(/"(\w+)":/g, '$1:')
  }
}
