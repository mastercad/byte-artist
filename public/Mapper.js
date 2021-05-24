class Mapper {

  /**
   * Map from data into object member, depending on given map.
   * 
   * @param {array|hash} data 
   * @param {object} target 
   * @param {array|hash} map 
   */
  static map(data, target, map) {
    for (let entry in data) {
      let mapKey = data[entry].name;
      if (undefined === map[mapKey]
        || undefined === target[Mapper.normalize(map[mapKey])]
      ) {
        continue;
      }
      target[Mapper.normalize(map[mapKey])] = Mapper.typifyValue(
        Mapper.checkPreSigned('!', map[mapKey].substr(0, 1)) ? 
          !data[entry].value : data[entry].value
      );
    }

    return target;
  }

  static normalize(value) {
    if (Mapper.checkPreSigned('!', value)) {
      return value.substr(1, value.length -1);
    }
    return value;
  }

  static checkPreSigned(sign, value) {
    return sign === value.substr(0, sign.length);
  }

  static typifyValue(value) {
    if ("false" == value) {
      return false;
    }
    if ("true" == value) {
      return true;
    }
    return value;
  }
}

export default Mapper