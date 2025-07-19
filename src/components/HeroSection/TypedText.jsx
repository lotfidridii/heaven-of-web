import React, { memo } from 'react';
import PropTypes from 'prop-types';
import { ReactTyped } from "react-typed";

const DEFAULT_STRINGS = [
  "Conception de sites Web", 
  "Référencement Naturel", 
  "Conception graphique", 
  "Stratégie de contenu"
];

const TypedText = memo(({ 
  strings = DEFAULT_STRINGS, 
  typeSpeed = 50, 
  backSpeed = 50, 
  loop = true,
  className = "typed-text"
}) => {
  return (
    <div className={className} aria-live="polite" aria-label="Services offerts par Heaven of Web">
      <ReactTyped 
        strings={strings} 
        typeSpeed={typeSpeed} 
        backSpeed={backSpeed} 
        loop={loop}
        aria-label="Services offerts par Heaven of Web"
      />
    </div>
  );
});

TypedText.propTypes = {
  strings: PropTypes.arrayOf(PropTypes.string),
  typeSpeed: PropTypes.number,
  backSpeed: PropTypes.number,
  loop: PropTypes.bool,
  className: PropTypes.string,
};

TypedText.displayName = 'TypedText';

export default TypedText;
