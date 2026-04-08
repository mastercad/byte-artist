class StringHelper {
    static cleanUpFromNotVisibleChars(content) {
        return content.replace(/[^\x20-\x7E]/g, '');
    }

    static replaceNewLineWithBreak(content) {
        return content.replace(/<(.|\n)*?>/g, '').replace(/(?:\r\n|\r|\n)/g, '<br>');
    }

    static contains(search, needle) {
      let regEx = new RegExp(needle);
      return null !== search.match(regEx);
    }
}

export default StringHelper;
