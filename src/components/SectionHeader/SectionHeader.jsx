import React, { memo } from 'react';
import PropTypes from 'prop-types';
import { motion } from "framer-motion";
import "./SectionHeader.css";

const SectionHeader = memo(({ Align, SectionSubTitle, SectionTitle }) => {
  return (
    <motion.div
      initial={{ scale: 0.8, opacity: 0 }}
      whileInView={{ scale: 1, opacity: 1 }}
      transition={{ duration: 0.6 }}
      viewport={{ once: true }}
      className={Align}
    >
      <div className="section-header">
        <h4>{SectionSubTitle}</h4>
        <h2>{SectionTitle}</h2>
      </div>
    </motion.div>
  );
});

SectionHeader.propTypes = {
  Align: PropTypes.string,
  SectionSubTitle: PropTypes.string.isRequired,
  SectionTitle: PropTypes.string.isRequired,
};

SectionHeader.defaultProps = {
  Align: '',
};

SectionHeader.displayName = 'SectionHeader';

export default SectionHeader;
