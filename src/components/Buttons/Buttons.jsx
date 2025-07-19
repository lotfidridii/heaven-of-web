import React, { memo } from 'react';
import PropTypes from 'prop-types';
import "./Buttons.css";

const Buttons = memo(({ href, btnName, onClick, ariaLabel, target, rel }) => {
  const handleClick = (e) => {
    if (onClick) {
      e.preventDefault();
      onClick(e);
    }
  };

  return (
    <a 
      className="btn" 
      href={href}
      onClick={handleClick}
      aria-label={ariaLabel || btnName}
      target={target}
      rel={target === '_blank' ? 'noopener noreferrer' : rel}
    >
      {btnName}
    </a>
  );
});

Buttons.propTypes = {
  href: PropTypes.string.isRequired,
  btnName: PropTypes.string.isRequired,
  onClick: PropTypes.func,
  ariaLabel: PropTypes.string,
  target: PropTypes.string,
  rel: PropTypes.string,
};

Buttons.defaultProps = {
  onClick: null,
  ariaLabel: null,
  target: null,
  rel: null,
};

Buttons.displayName = 'Buttons';

export default Buttons;
