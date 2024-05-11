class YAMLish
{
  static parse(yamlLike) {
    return JSON.parse(
      yamlLike.replace(/(\w+):/g, '"$1":')  
    )
  }

  static dump(obj) {
    return JSON.stringify(obj)
      .replace(/"(\w+)":/g, '$1:')     // no " for keys
      .replace(/(,|:)(?!\s)/g, '$1 ')  // colon or comma followed by a space of none (Symfony yaml requirement)
      // .replace(/(,|:)/g, '$1 ')     //   neg look head no space
      // .replace(/\s+/g, ' ')         // single space only
  }
}
