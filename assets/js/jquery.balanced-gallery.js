;(function (jQuery, window, document, undefined ) {
    "use strict";

    var pluginName = 'BalancedGallery',
        balancedGallery,
        defaults = {
            autoResize: true,
            background: null,
            idealHeight: null,
            idealWidth: null,
            maintainOrder: true,
            orientation: 'horizontal',
            padding: 5,
            shuffleUnorderedPartitions: true,
            viewportHeight: null,
            viewportWidth: null
        },
        ALL_CHILDREN_LOADED = 'ALL_CHILDREN_LOADED',
        resizeTimeout = null,
        RADIX = 10;

    //this wrapper prevents multiple instantiations of the plugin:
    jQuery.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!jQuery.data(this, 'plugin_' + pluginName)) {
                jQuery.data(this, 'plugin_' + pluginName, new BalancedGallery( this, options ));
            }
        });
    };

    function BalancedGallery( element, options ) {
        balancedGallery = this; // for contexts when 'this' doesn't refer to the BalancedGallery class.
        this.element = element;
        this.elementChildren = jQuery(element).children('*');
        this.options = jQuery.extend( {}, defaults, options); // merge arg options and defaults

        if(this.options.autoResize) {
            this.unadulteratedHtml = jQuery(this.element).html();
            this.unadulteratedCSS = getUnadulteratedCss();
            this.unadulteratedOptions = jQuery.extend({}, this.options);
            setupAutoResize();
        }

        this.init();
        this.createGallery();
    }

    function getUnadulteratedCss() {
        var element = jQuery(balancedGallery.element);
        //only the properties modified by the plugin
        return {
            width:  jQuery(element[0]).outerWidth,
            height: jQuery(element[0]).outerHeight,
            background: jQuery(element).css('background'),
            paddingLeft: jQuery(element).css('padding-left'),
            paddingTop: jQuery(element).css('padding-top'),
            overflow: jQuery(element).css('overflow'),
            fontSize: jQuery(element).css('font-size')
        };
    }

    function setupAutoResize() {
        jQuery(window).resize(function() {
            clearTimeout(resizeTimeout);

            resizeTimeout = setTimeout(function() {
                balancedGallery.recreate();
            }, 500);
        });
    }

    BalancedGallery.prototype.recreate = function () {
        jQuery(this.element).on(ALL_CHILDREN_LOADED, function() {
            if (undefined !== jQuery(balancedGallery.element).data('orig-height')) {
                jQuery(balancedGallery.element).css('height', jQuery(balancedGallery.element).data('orig-height'));
            }

            balancedGallery.init();
            balancedGallery.createGallery();
        });
        this.reset();
    };

    BalancedGallery.prototype.reset = function() {
        var childCount = this.elementChildren.length;

        jQuery(this.element).html(this.unadulteratedHtml);
        jQuery(this.element).css(this.unadulteratedCSS);
        this.options = jQuery.extend({}, this.unadulteratedOptions);
        this.elementChildren = jQuery(this.element).children('*');

        var loadedChildren = 0;
        this.elementChildren.each(function() {
            jQuery(this).load(function() {
                if(++loadedChildren === childCount) {
                    jQuery(balancedGallery.element).trigger(ALL_CHILDREN_LOADED);
                }
            });
        });
    };

    BalancedGallery.prototype.init = function () {
        if(this.options.viewportWidth === null) {
            this.options.viewportWidth = jQuery(this.element).width();
        }

        if(this.options.viewportHeight === null) {
            this.options.viewportHeight = jQuery(this.element).height();
        }

        if(this.options.idealWidth === null) {
            this.options.idealWidth = jQuery(this.element).width() / 4;
        }

        if(this.options.idealHeight === null) {
            this.options.idealHeight = jQuery(this.element).height() / 2;
        }

        if(this.options.background !== null) {
            jQuery(this.element).css({background: this.options.background});
        }

        this.elementChildren.css({display: 'inline-block', padding: 0, margin: 0});
        var padding = this.options.padding + 'px';
        jQuery(this.element).css({fontSize: 0, paddingTop: padding, paddingLeft: padding});
    };

    BalancedGallery.prototype.createGallery = function() {
        var orientation = (this.options.orientation).toLowerCase();
        if(orientation === 'horizontal') {
            createHorizontalGallery();
        } else if(orientation === 'vertical') {
            createVerticalGallery();
        } else {
            throw("BalancedGallery: Invalid Orientation.");
        }
    };

    function createHorizontalGallery() {
        var rows, weights, partitions;
        rows = getRows();
        if(rows === 0) {
            balancedGallery.fallbackToStandardSize();
        } else {
            weights = getWidthWeights();
            partitions = getPartitions(weights, rows);
            resizeHorizontalElements(partitions);
        }


        if(balancedGallery.element !== document.body) {
            //jQuery(balancedGallery.element).css({overflow:'scroll'});
            if (undefined === jQuery(balancedGallery.element).data('orig-height')) {
                jQuery(balancedGallery.element).data('orig-height', jQuery(balancedGallery.element).css('height'));
            }
            jQuery(balancedGallery.element).css({height:'auto'});
        }
    }

    function createVerticalGallery() {
        var cols, weights, partitions;
        cols = getColumns();
        if(cols === 0) {
            balancedGallery.fallbackToStandardSize();
        } else {
            weights = getHeightWeights();
            partitions = getPartitions(weights, cols);
            orientElementsVertically(partitions);
            resizeVerticalElements(partitions);
        }
    }

    function getRows () {
        return Math.round( collectiveIdealWidth() / (balancedGallery.options.viewportWidth - balancedGallery.options.padding) );
    }

    function getColumns() {
        return Math.round( collectiveIdealHeight() / (balancedGallery.options.viewportHeight - balancedGallery.options.padding) );
    }

    function collectiveIdealWidth() {
        var sum = 0;
        balancedGallery.elementChildren.each(function () {
            sum += idealWidth(jQuery(this));
        });
        return sum;
    }

    function collectiveIdealHeight() {
        var sum = 0;
        balancedGallery.elementChildren.each(function () {
            sum += idealHeight(jQuery(this));
        });
        return sum;
    }

    function idealWidth(jQueryimage) {
        return aspectRatio(jQueryimage) * (balancedGallery.options.idealHeight + balancedGallery.options.padding);
    }

    function idealHeight(jQueryimage) {
        return (1/aspectRatio(jQueryimage)) * (balancedGallery.options.idealWidth + balancedGallery.options.padding);
    }

    BalancedGallery.prototype.fallbackToStandardSize = function() {
        var idealHeight = this.options.idealHeight;
        this.elementChildren.each(function () {
            jQuery(this).height( idealHeight );
            try {
                jQuery(this).width(idealWidth(jQuery(this)));
            } catch (e) {
                console.log(e.message);
            }
        });
    };

    function getWidthWeights() {
        return balancedGallery.elementChildren.map(function () {
            var weight = parseInt( aspectRatio(jQuery(this)) * 100, RADIX );
            return {element: this, weight: weight };
        });
    }

    function getHeightWeights() {
        return balancedGallery.elementChildren.map(function () {
            var weight = parseInt( (1/aspectRatio(jQuery(this))) * 100, RADIX );
            return {element: this, weight: weight };
        });
    }

    function getPartitions(weights, sections) {
        if(balancedGallery.options.maintainOrder) {
            return getOrderedPartition(weights, sections);
        } else {
            var partitions = getUnorderedPartition(weights, sections);
            if(balancedGallery.options.shuffleUnorderedPartitions) {
                partitions = shufflePartitions(partitions);
            }
            reorderElements(partitions);
            return partitions;
        }
    }

    function getOrderedPartition(weights, sections) {
        var elementCount = weights.length;

        if(sections <= 0) {
            return [];
        }

        if(sections >= elementCount) {
            return weights.map(function(key, value) { return [([value])]; });
        }

        var solution  = createSolutionTable(weights, sections);
        elementCount -= 1;
        sections -= 2;
        var partitions = [];

        while(sections >= 0) {
            var results = [];
            for(var f = (solution[elementCount-1][sections]+1); f < elementCount+1; f++){
                results.push(weights[f]);
            }
            partitions = [results].concat(partitions);

            elementCount = solution[elementCount-1][sections];
            sections -= 1;
        }

        var results2 = [];
        for(var r = 0; r < elementCount+1; r++) {
            results2.push(weights[r]);
        }
        return [results2].concat(partitions);
    }

    // Used as part of the ordered partition function:
    function createSolutionTable(weights, sections) {
        var elementCount = weights.length;

        var table = [];
        for (var i = 0; i < elementCount; i++) {
            var res = [];
            for (var j = 0; j < sections; j++) {
                res.push(0);
            }
            table.push(res);
        }

        var solution = [];
        for (var k = 0; k < elementCount-1; k++) {
            var res2 = [];
            for (var l = 0; l < sections-1; l++) {
                res2.push(0);
            }
            solution.push(res2);
        }

        for(var m = 0; m < elementCount; m++) {
            table[m][0] = weights[m].weight + (m !== 0 ? table[m-1][0] : 0);
        }
        for(var n = 0; n < sections; n++) {
            table[0][n] = weights[0].weight;
        }

        var subArraySort = function(a, b){ return a[0]-b[0]; };
        for(var p = 1; p < elementCount; p++) {
            for(var q = 1; q < sections; q++) {
                var results = [];
                for (var r = 0; r < p; r++) {
                    results.push([ Math.max( table[r][q - 1], table[p][0]-table[r][0] ), r ]);
                }
                var arr = results.sort(subArraySort)[0];
                table[p][q] = arr[0];
                solution[p-1][q-1] = arr[1];
            }
        }

        return solution;
    }

    function getUnorderedPartition(weights, sections) {
        var sortedWeights = weights.sort(function(a,b){ return b.weight - a.weight; });

        var partitions = new Array(sections);
        for (var i=0; i <sections; i++) { partitions[i] = []; }


        for(var j = 0; j < sortedWeights.length; j++) {
            var shortestPartition = partitions[0];
            var shortestPartitionWeight = getPartitionWeight(shortestPartition);

            for(var k = 0; k < partitions.length; k++) {
                var pWeight = getPartitionWeight(partitions[k]);
                if(pWeight < shortestPartitionWeight) {
                    shortestPartition = partitions[k];
                    shortestPartitionWeight = pWeight;
                }
            }
            shortestPartition.push(sortedWeights[j]);
        }

        return partitions;
    }

    function getPartitionWeight(partition) {
        var weight = 0;
        jQuery.each(partition, function(index, value) {
            weight += value.weight;
        });
        return weight;
    }

    function shufflePartitions(partitions) {
        for(var i = 0; i < partitions.length; i++) {
            partitions[i] = shuffleArray(partitions[i]);
        }
        return shuffleArray(partitions);
    }

    function reorderElements(partitions) {
        jQuery(balancedGallery.element).html(''); //remove all elements
        for(var i = 0; i < partitions.length; i++) {
            var subPartition = partitions[i];
            for(var j = 0; j < subPartition.length; j++) {
                jQuery(balancedGallery.element).append(subPartition[j].element);
            }
        }
    }

    function resizeHorizontalElements(partitions) {
        var padding = balancedGallery.options.padding;
        for(var i = 0; i < partitions.length; i++) {
            var summedRowRatios = 0;
            for(var j = 0; j < partitions[i].length; j++) {
                summedRowRatios += aspectRatio( jQuery(partitions[i][j].element) );
            }
            for(var k = 0; k < partitions[i].length; k++) {
                var jQueryimage = jQuery(partitions[i][k].element);
                var rawImgHeight = (balancedGallery.options.viewportWidth - padding) / summedRowRatios  ;
                var imgHeight = parseInt( rawImgHeight, RADIX );
                var imgWidth = parseInt( imgHeight * aspectRatio(jQueryimage), RADIX ) - padding;
                jQueryimage.width(imgWidth);
                jQueryimage.height(imgHeight);
                jQueryimage.css({margin: 0, marginRight:padding+'px', marginBottom:padding+'px'});
            }
        }
    }

    function resizeVerticalElements(partitions) {
        var padding = balancedGallery.options.padding;
        for(var i = 0; i < partitions.length; i++) {
            var summedColRatios = 0;
            for(var j = 0; j < partitions[i].length; j++) {
                summedColRatios += 1/aspectRatio( jQuery(partitions[i][j].element) );
            }
            for(var k = 0; k < partitions[i].length; k++) {
                var jQueryimage = jQuery(partitions[i][k].element);
                var rawImgWidth = (balancedGallery.options.viewportHeight - padding) / summedColRatios  ;
                var imgWidth = parseInt( rawImgWidth, RADIX );
                var imgHeight = parseInt( imgWidth * (1/aspectRatio(jQueryimage)), RADIX ) - padding;
                jQueryimage.width(imgWidth);
                jQueryimage.height(imgHeight);
                jQueryimage.css({margin: 0, marginRight:padding+'px', marginBottom:padding+'px'});
            }
        }

        //Resize Container for horizontal scrolling
        jQuery('.balanced-gallery-column').css({display:'inline-block', padding: 0, margin: 0});
        jQuery(balancedGallery.container).width(function() {
            var sum = 0;
            jQuery('.balanced-gallery-column').each(function() { sum += (jQuery(this).width()); });
            return sum;
        }());

        if(balancedGallery.element !== document.body) {
            jQuery(balancedGallery.element).css({overflow:'hidden'});
        }

        //If there's horizontal overflow the scrollbar causes vertical scrolling
        if(balancedGallery.options.viewportHeight !== balancedGallery.element.clientHeight) {
            balancedGallery.options.viewportHeight = balancedGallery.element.clientHeight - balancedGallery.options.padding;
            jQuery(balancedGallery.element).height(balancedGallery.options.viewportHeight - balancedGallery.options.padding);
            if(balancedGallery.element === document.body) {
                resizeVerticalElements(partitions);
            }
        }
    }

    function orientElementsVertically(partitions) {
        var jQueryelement = jQuery(balancedGallery.element),
            jQuerycontainer;
        jQueryelement.html(''); //clear the images
        jQueryelement.css({overflow: 'scroll'});
        if(balancedGallery.element !== document.body) {
            jQuerycontainer = jQuery('<div id="balanced-gallery-col-container"></div>');
            jQueryelement.append(jQuerycontainer[0]);
        } else {
            jQuerycontainer = jQuery(balancedGallery.element);
        }
        balancedGallery.container = jQuerycontainer[0];


        for(var i = 0; i < partitions.length; i++) {
            var colName = 'balanced-gallery-col'+i;
            var column = '<div class="balanced-gallery-column" id="'+colName+'"></div>';
            jQuerycontainer.append(column);
            for(var j = 0; j < partitions[i].length; j++) {
                var child = partitions[i][j].element;
                var jQuerycol = jQuery(jQuerycontainer.find("div#"+colName));
                jQuerycol.append(child).append('<br />');
            }
        }

    }

    function aspectRatio(jQueryimage) {
        var padding = balancedGallery.options.padding;
        return (jQueryimage.width()+padding) / (jQueryimage.height()+padding);
    }

    function shuffleArray(array) {
        var counter = array.length, temp, index;
        while (counter--) {
            index = (Math.random() * counter) | 0; //not a typo
            temp = array[counter];
            array[counter] = array[index];
            array[index] = temp;
        }
        return array;
    }

})( jQuery, window, document );