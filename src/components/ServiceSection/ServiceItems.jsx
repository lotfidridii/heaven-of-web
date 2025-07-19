import React, { memo } from 'react';
import PropTypes from 'prop-types';
import { Col } from "react-bootstrap";

const ServiceItem = memo(({ ServiceIcon, ServiceTitle, ServiceDescription }) => {
  return (
    <Col lg="6">
      <article className="service-item">
        <div className="service-icon" aria-hidden="true">
          <i>{ServiceIcon}</i>
        </div>
        <div className="service-text">
          <h3>{ServiceTitle}</h3>
          <p>{ServiceDescription}</p>
        </div>
      </article>
    </Col>    
  );
});

ServiceItem.propTypes = {
  ServiceIcon: PropTypes.element.isRequired,
  ServiceTitle: PropTypes.string.isRequired,
  ServiceDescription: PropTypes.string.isRequired,
};

ServiceItem.displayName = 'ServiceItem';

export default ServiceItem;
